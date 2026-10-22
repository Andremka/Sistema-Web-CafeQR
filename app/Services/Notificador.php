<?php

namespace App\Services;

use App\Mail\CafeQrMail;
use App\Models\Correo;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

/**
 * Todos los correos del sistema salen por aquí.
 *  - Si Gmail falla, se reintenta una vez con una conexión nueva.
 *  - Si igual falla, NO se rompe la página: se guarda el error en la tabla "correos"
 *    (el administrador lo ve en Panel → Correos y lo puede reenviar) y en storage/logs/laravel.log.
 */
class Notificador
{
    public static function enviar(User $usuario, string $asunto, string $vista, array $datos = []): bool
    {
        $mail = new CafeQrMail($asunto, $vista, $datos + ['usuario' => $usuario]);
        $tipo = str_replace('emails.', '', $vista);

        $html = null;
        try {
            $html = $mail->render();
        } catch (\Throwable $e) {
            Log::error('CafeQR: no se pudo armar el correo "'.$asunto.'": '.$e->getMessage());
        }

        [$ok, $error, $intentos] = self::mandar(fn () => Mail::to($usuario->email)->send($mail));

        self::registrar($usuario, $usuario->email, $asunto, $tipo, $ok, $error, $intentos, $html);

        return $ok;
    }

    /** Reenvía un correo guardado (botón "Reenviar" del administrador). */
    public static function reenviar(Correo $correo): bool
    {
        if (! $correo->html) {
            return false;
        }

        [$ok, $error, $intentos] = self::mandar(fn () => Mail::html($correo->html, function ($m) use ($correo) {
            $m->to($correo->para)->subject($correo->asunto);
        }));

        $correo->update([
            'estado' => $ok ? (self::modoPrueba() ? Correo::PRUEBA : Correo::ENVIADO) : Correo::FALLIDO,
            'error' => $error,
            'intentos' => min(255, $correo->intentos + $intentos),
        ]);

        return $ok;
    }

    /** Intenta enviar; si falla, reinicia la conexión con Gmail y prueba una vez más. */
    private static function mandar(callable $envio): array
    {
        $error = null;
        for ($intento = 1; $intento <= 2; $intento++) {
            try {
                $envio();

                return [true, null, $intento];
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                Log::warning('CafeQR: intento '.$intento.' de envío fallido: '.$error);
                app('mail.manager')->purge();   // conexión nueva para el reintento
                if ($intento === 1 && ! app()->runningUnitTests()) {
                    usleep(800_000);
                }
            }
        }

        return [false, self::errorEntendible($error), 2];
    }

    private static function registrar(?User $usuario, string $para, string $asunto, string $tipo, bool $ok, ?string $error, int $intentos, ?string $html): void
    {
        if (! $ok) {
            Log::error('CafeQR: no se pudo enviar el correo "'.$asunto.'" a '.$para.': '.$error);
        }

        try {
            if (Schema::hasTable('correos')) {
                Correo::create([
                    'user_id' => $usuario?->id,
                    'para' => $para,
                    'asunto' => mb_substr($asunto, 0, 200),
                    'tipo' => mb_substr($tipo, 0, 40),
                    'estado' => $ok ? (self::modoPrueba() ? Correo::PRUEBA : Correo::ENVIADO) : Correo::FALLIDO,
                    'error' => $error,
                    'intentos' => $intentos,
                    'html' => $html,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('CafeQR: no se pudo guardar el historial del correo: '.$e->getMessage());
        }
    }

    /** Traduce los errores típicos de Gmail a algo que se entienda. */
    public static function errorEntendible(?string $error): ?string
    {
        if ($error === null) {
            return null;
        }
        $e = strtolower($error);

        $pista = match (true) {
            str_contains($e, '535') || str_contains($e, 'username and password not accepted') || str_contains($e, 'authenticat')
                => 'Gmail rechazó el usuario o la contraseña de aplicación (revisa MAIL_USERNAME y MAIL_PASSWORD en el .env).',
            str_contains($e, '550') || str_contains($e, 'does not exist') || str_contains($e, 'no such user') || str_contains($e, 'recipient')
                => 'El correo de destino no existe o lo rechazó el servidor del destinatario.',
            str_contains($e, 'connection') || str_contains($e, 'timed out') || str_contains($e, 'getaddrinfo') || str_contains($e, 'network')
                => 'No hay conexión con Gmail (revisa el internet de la computadora).',
            str_contains($e, '421') || str_contains($e, '454') || str_contains($e, 'too many') || str_contains($e, 'limit')
                => 'Gmail limitó los envíos por un momento. Reenvíalo en unos minutos.',
            default => null,
        };

        return ($pista ? $pista.' · ' : '').mb_substr($error, 0, 400);
    }

    /** true si el correo no sale de verdad (MAIL_MAILER=log o array). */
    public static function modoPrueba(): bool
    {
        return in_array(config('mail.default'), ['log', 'array'], true);
    }

    /* ----------------------------- Correos del sistema ----------------------------- */

    /** Enlace firmado para confirmar que el correo es del usuario (vale 7 días). */
    public static function enlaceVerificacion(User $usuario): string
    {
        return URL::temporarySignedRoute('correo.verificar', now()->addDays(7), [
            'id' => $usuario->id,
            'hash' => sha1($usuario->email),
        ]);
    }

    public static function bienvenida(User $usuario): bool
    {
        return self::enviar($usuario, '¡Bienvenido a CafeQR, '.strtok($usuario->name, ' ').'! Confirma tu correo', 'emails.bienvenida', [
            'url' => self::enlaceVerificacion($usuario),
        ]);
    }

    public static function recuperar(User $usuario, string $url, int $minutos): bool
    {
        return self::enviar($usuario, 'Recupera tu contraseña · CafeQR', 'emails.recuperar', [
            'url' => $url,
            'nombre' => $usuario->name,
            'minutos' => $minutos,
        ]);
    }

    public static function codigo(User $usuario, string $codigo, Pago $pago): bool
    {
        return self::enviar($usuario, 'Tu código de verificación CafeQR: '.$codigo, 'emails.codigo', [
            'codigo' => $codigo,
            'pago' => $pago,
            'minutos' => \App\Models\Verificacion::MINUTOS,
        ]);
    }

    public static function pagoConfirmado(Pedido $pedido): bool
    {
        return self::enviar($pedido->user, 'Pago confirmado · Pedido #'.$pedido->id.' · CafeQR', 'emails.pago-confirmado', [
            'pedido' => $pedido,
        ]);
    }

    public static function enPreparacion(Pedido $pedido): bool
    {
        return self::enviar($pedido->user, 'Tu pedido #'.$pedido->id.' está en preparación · CafeQR', 'emails.en-preparacion', [
            'pedido' => $pedido,
            'minutos' => $pedido->tiempo_estimado,
        ]);
    }

    public static function listo(Pedido $pedido): bool
    {
        return self::enviar($pedido->user, '¡Tu pedido #'.$pedido->id.' está listo para recoger! · CafeQR', 'emails.listo', [
            'pedido' => $pedido,
        ]);
    }

    public static function cancelado(Pedido $pedido): bool
    {
        return self::enviar($pedido->user, 'Pedido #'.$pedido->id.' cancelado · CafeQR', 'emails.cancelado', [
            'pedido' => $pedido,
        ]);
    }
}
