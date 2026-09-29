<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    private function autorizar(Pedido $pedido): void
    {
        abort_unless(
            $pedido->user_id === auth()->id() || auth()->user()->esAdministrador(),
            403
        );
    }

    public function confirmacion(Pedido $pedido): View
    {
        $this->autorizar($pedido);

        return view('pedidos.confirmacion', ['pedido' => $pedido->load('pago')]);
    }

    /** Historia de usuario: factura de pago con todos los detalles. */
    public function factura(Pedido $pedido): View
    {
        $this->autorizar($pedido);

        return view('pedidos.factura', [
            'pedido' => $pedido->load('detalle', 'pago', 'usuario'),
        ]);
    }

    /**
     * Simula el envío del código de recojo por correo y/o SMS.
     * TODO: conectar un Mailable real y un proveedor de SMS antes de producción.
     */
    public function enviarCodigo(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->autorizar($pedido);

        $datos = $request->validate([
            'enviar_correo' => ['nullable', 'boolean'],
            'enviar_sms' => ['nullable', 'boolean'],
            'correo' => ['required_if:enviar_correo,1', 'nullable', 'email'],
            'telefono' => ['required_if:enviar_sms,1', 'nullable', 'string', 'min:7'],
        ]);

        if (! ($datos['enviar_correo'] ?? false) && ! ($datos['enviar_sms'] ?? false)) {
            return back()->with('error', 'Elige al menos un medio: correo o mensaje de texto.');
        }

        $mensajes = [];
        if ($datos['enviar_correo'] ?? false) {
            $mensajes[] = "Código enviado por correo a {$datos['correo']}";
        }
        if ($datos['enviar_sms'] ?? false) {
            $mensajes[] = "Código enviado por SMS a {$datos['telefono']}";
        }

        return back()->with('exito', implode(' · ', $mensajes));
    }

    public function seguimiento(Pedido $pedido): View
    {
        $this->autorizar($pedido);

        return view('pedidos.seguimiento', ['pedido' => $pedido]);
    }

    /** Marca el pedido como listo para recoger (simulación del personal de cafetería). */
    public function marcarListo(Pedido $pedido): RedirectResponse
    {
        $this->autorizar($pedido);

        $pedido->update(['listo' => true]);

        return back()->with('exito', 'Tu pedido está listo para recoger');
    }

    /** Historia de usuario: HU-15 — Consultar historial de pedidos. */
    public function misPedidos(Request $request): View
    {
        $pedidos = $request->user()
            ->pedidos()
            ->with('detalle', 'pago')
            ->latest()
            ->get();

        return view('pedidos.mis-pedidos', [
            'pedidos' => $pedidos,
            'totalGastado' => $pedidos->sum('total'),
        ]);
    }
}
