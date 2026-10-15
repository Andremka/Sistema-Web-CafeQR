<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Services\Notificador;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Caja: confirmar pagos (QR por verificar y efectivo). Empleados y administrador. */
class CajaController extends Controller
{
    public function pagos(): View
    {
        $conPedido = ['pedido.user', 'pedido.detalles'];

        return view('caja.pagos', [
            'porVerificar' => Pago::where('estado', 'por_verificar')->with($conPedido)->oldest()->get(),
            'efectivo' => Pago::where('estado', 'por_cobrar')->with($conPedido)->oldest()->get(),
            'esperandoCodigo' => Pago::where('estado', 'recibido')->with($conPedido)->oldest()->get(),
            'modoPrueba' => Notificador::modoPrueba(),
        ]);
    }

    /** "Recibí el dinero": en QR envía el código al correo; en efectivo termina el pago. */
    public function recibido(Pago $pago): RedirectResponse
    {
        if ($pago->pedido->estado !== Pedido::PENDIENTE_PAGO) {
            return back()->with('error', 'Ese pedido ya no está pendiente de pago.');
        }

        if ($pago->estado === 'por_cobrar') {
            $pago->completar();

            return back()->with('exito', 'Cobro en efectivo registrado. El cliente recibió su código de recojo y su factura.');
        }

        if ($pago->estado === 'por_verificar') {
            $enviado = $pago->marcarRecibido();

            return back()->with($enviado
                ? ['exito' => 'Pago confirmado. Le enviamos el código de 6 dígitos al correo del cliente.']
                : ['error' => 'Pago confirmado, pero no se pudo enviar el correo. Revisa la configuración MAIL_* del .env.']);
        }

        return back()->with('error', 'Ese pago ya fue atendido.');
    }

    public function rechazar(Pago $pago): RedirectResponse
    {
        if (! in_array($pago->estado, ['por_verificar', 'por_cobrar'], true)) {
            return back()->with('error', 'Ese pago ya fue atendido.');
        }

        $pago->rechazar();

        return back()->with('exito', 'Pago rechazado. El cliente puede volver a intentarlo.');
    }
}
