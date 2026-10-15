<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\Notificador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Entregas: preparar (con tiempo estimado), marcar listo, entregar, cancelar y reembolsar. */
class EntregaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q'));

        $activos = Pedido::query()
            ->whereIn('estado', Pedido::ACTIVOS)
            ->with(['user', 'detalles'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($q2) use ($buscar) {
                    $q2->where('codigo', 'like', '%'.strtoupper($buscar).'%')
                        ->orWhere('id', ctype_digit($buscar) ? (int) $buscar : 0)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$buscar.'%'));
                });
            })
            ->orderByRaw("CASE estado WHEN 'listo' THEN 0 WHEN 'en_preparacion' THEN 1 ELSE 2 END")
            ->orderBy('pagado_en')
            ->get();

        return view('caja.entregas', [
            'activos' => $activos,
            'buscar' => $buscar,
            'reembolsos' => Pedido::where('reembolso_pendiente', true)->with('user')->latest('cancelado_en')->get(),
            'entregadosHoy' => Pedido::where('estado', Pedido::ENTREGADO)->whereDate('entregado_en', today())
                ->with('user')->latest('entregado_en')->get(),
            'tiempoPorDefecto' => max(1, (int) config('cafeqr.tiempo_preparacion')),
        ]);
    }

    /** Pasa a "En preparación" y avisa al cliente cuánto va a demorar. */
    public function preparar(Request $request, Pedido $pedido): RedirectResponse
    {
        if (! in_array($pedido->estado, [Pedido::PAGADO, Pedido::EN_PREPARACION], true)) {
            return back()->with('error', 'Solo se pueden preparar pedidos pagados.');
        }

        $datos = $request->validate([
            'minutos' => ['required', 'integer', 'min:1', 'max:180'],
        ], [
            'minutos.required' => 'Indica en cuántos minutos estará listo el pedido.',
            'minutos.integer' => 'Los minutos deben ser un número entero.',
            'minutos.min' => 'El tiempo mínimo es 1 minuto.',
            'minutos.max' => 'El tiempo máximo es 180 minutos.',
        ]);

        $pedido->update([
            'estado' => Pedido::EN_PREPARACION,
            'tiempo_estimado' => (int) $datos['minutos'],
            'preparacion_inicio' => now(),
        ]);

        return back()->with($this->resultadoCorreo(
            Notificador::enPreparacion($pedido->fresh(['user', 'detalles'])),
            'Pedido #'.$pedido->id.' en preparación (listo en ~'.$datos['minutos'].' min).'
        ));
    }

    public function listo(Pedido $pedido): RedirectResponse
    {
        if (! in_array($pedido->estado, [Pedido::PAGADO, Pedido::EN_PREPARACION], true)) {
            return back()->with('error', 'Ese pedido no se puede marcar como listo.');
        }

        $pedido->update(['estado' => Pedido::LISTO, 'listo_en' => now()]);

        return back()->with($this->resultadoCorreo(
            Notificador::listo($pedido->fresh(['user', 'detalles'])),
            'Pedido #'.$pedido->id.' marcado como LISTO.'
        ));
    }

    public function entregar(Pedido $pedido): RedirectResponse
    {
        if ($pedido->estado !== Pedido::LISTO) {
            return back()->with('error', 'Primero marca el pedido como listo.');
        }

        $pedido->update(['estado' => Pedido::ENTREGADO, 'entregado_en' => now()]);

        return back()->with('exito', 'Pedido #'.$pedido->id.' entregado.');
    }

    public function cancelar(Request $request, Pedido $pedido): RedirectResponse
    {
        if (! $pedido->puedeCancelarPersonal()) {
            return back()->with('error', 'Ese pedido ya no se puede cancelar.');
        }

        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:200'],
        ], [
            'motivo.required' => 'Escribe el motivo de la cancelación.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede pasar de 200 caracteres.',
        ]);

        $pedido->cancelar('personal', $datos['motivo']);

        return back()->with($this->resultadoCorreo(
            Notificador::cancelado($pedido->fresh(['user', 'detalles'])),
            'Pedido #'.$pedido->id.' cancelado.'.($pedido->reembolso_pendiente ? ' Queda un reembolso pendiente.' : '')
        ));
    }

    public function reembolsar(Pedido $pedido): RedirectResponse
    {
        if (! $pedido->reembolso_pendiente) {
            return back()->with('error', 'Ese pedido no tiene reembolso pendiente.');
        }

        $pedido->update(['reembolso_pendiente' => false, 'reembolsado_en' => now()]);

        return back()->with('exito', 'Reembolso del pedido #'.$pedido->id.' registrado.');
    }

    private function resultadoCorreo(bool $enviado, string $mensaje): array
    {
        if (! $enviado) {
            return ['error' => $mensaje.' Pero NO se pudo enviar el correo al cliente: revisa MAIL_* en el .env (php artisan correo:probar tucorreo).'];
        }
        if (Notificador::modoPrueba()) {
            return ['exito' => $mensaje.' (Modo prueba: el correo no sale a internet; configura Gmail en el .env.)'];
        }

        return ['exito' => $mensaje.' Se le envió un correo al cliente.'];
    }
}
