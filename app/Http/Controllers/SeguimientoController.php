<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\Notificador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoController extends Controller
{
    public function mostrar(Request $request, Pedido $pedido): View|RedirectResponse
    {
        $this->soloDueno($request, $pedido);

        if (in_array($pedido->estado, [Pedido::CARRITO, Pedido::PENDIENTE_PAGO], true)) {
            return redirect()->route('pedido.continuar');
        }

        $pedido->load(['detalles', 'factura']);

        return view('pedidos.seguimiento', ['pedido' => $pedido]);
    }

    /** Lo consulta la pantalla de seguimiento cada pocos segundos. */
    public function estado(Request $request, Pedido $pedido): JsonResponse
    {
        $this->soloDueno($request, $pedido);

        return response()->json([
            'estado' => $pedido->estado,
            'texto' => $pedido->estado_texto,
            'tiempo_estimado' => $pedido->tiempo_estimado,
            'minutos_restantes' => $pedido->minutosRestantes(),
            'listo_estimado' => $pedido->listo_estimado?->timezone(config('app.timezone'))->format('H:i'),
            'codigo' => $pedido->codigo,
        ]);
    }

    public function cancelar(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->soloDueno($request, $pedido);

        if (! $pedido->puedeCancelarCliente()) {
            return back()->with('error', 'Este pedido ya no se puede cancelar porque está '.mb_strtolower($pedido->estado_texto).'.');
        }

        $datos = $request->validate([
            'motivo' => ['nullable', 'string', 'max:200'],
        ], [
            'motivo.max' => 'El motivo no puede pasar de 200 caracteres.',
        ]);

        $pedido->cancelar('cliente', $datos['motivo'] ?? null);
        $pedido->load('user');

        $mensaje = 'Cancelaste el pedido #'.$pedido->id.'.';
        if ($pedido->reembolso_pendiente) {
            $mensaje .= ' Como ya habías pagado, acércate a la cafetería para que te devuelvan tu dinero.';
        }

        if (! Notificador::cancelado($pedido)) {
            $mensaje .= ' (No pudimos enviarte el correo de confirmación.)';
        }

        return redirect()->route('pedidos.historial')->with('exito', $mensaje);
    }

    private function soloDueno(Request $request, Pedido $pedido): void
    {
        abort_unless($pedido->user_id === $request->user()->id && $pedido->estado !== Pedido::CARRITO, 403);
    }
}
