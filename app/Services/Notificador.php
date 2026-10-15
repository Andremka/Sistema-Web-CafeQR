<?php

namespace App\Services;

use App\Mail\CafeQrMail;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Todos los correos del sistema salen por aquí.
 * Si el correo falla, NO se rompe el pedido: se anota en storage/logs/laravel.log y se devuelve false.
 */
class Notificador
{
    public static function enviar(User $usuario, string $asunto, string $vista, array $datos = []): bool
    {
        try {
            Mail::to($usuario->email)->send(new CafeQrMail($asunto, $vista, $datos + ['usuario' => $usuario]));

            return true;
        } catch (\Throwable $e) {
            Log::error('CafeQR: no se pudo enviar el correo "'.$asunto.'" a '.$usuario->email.': '.$e->getMessage());

            return false;
        }
    }

    /** true si el correo no sale de verdad (MAIL_MAILER=log o array). */
    public static function modoPrueba(): bool
    {
        return in_array(config('mail.default'), ['log', 'array'], true);
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
