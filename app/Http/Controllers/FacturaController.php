<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Support\NumeroALetras;
use App\Support\Qr;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function mostrar(Request $request, Pedido $pedido): View
    {
        $usuario = $request->user();
        abort_unless($pedido->user_id === $usuario->id || $usuario->esPersonal(), 403);

        $pedido->load(['factura', 'detalles', 'user']);
        abort_unless($pedido->factura, 404);

        return view('facturas.show', ['pedido' => $pedido] + self::datos($pedido));
    }

    /** Datos que usa el documento de la factura (también la pantalla de "gracias"). */
    public static function datos(Pedido $pedido): array
    {
        $factura = $pedido->factura;
        $total = (float) $pedido->total;

        return [
            'factura' => $factura,
            'emisor' => config('cafeqr.emisor'),
            'clienteNombre' => $pedido->razon_social ?: $pedido->user->name,
            'clienteDoc' => $pedido->nit_ci ?: 'S/N',
            'enLetras' => NumeroALetras::convertir($total),
            'qrFactura' => Qr::dataUri(sprintf(
                'CAFEQR|FACTURA %s|%s|%s',
                $factura->numero_formateado,
                number_format($total, 2, '.', ''),
                $factura->emitida_en->format('Y-m-d H:i')
            )),
        ];
    }
}
