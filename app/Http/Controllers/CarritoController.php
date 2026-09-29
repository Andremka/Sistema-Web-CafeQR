<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Historia de usuario: HU-09 — Seleccionar productos y generar pedido.
 * El carrito vive en la sesión hasta que el pedido se confirma con el pago;
 * recién en ese momento se persiste como Pedido + PedidoDetalle (HU-10).
 */
class CarritoController extends Controller
{
    public function agregar(Producto $producto): RedirectResponse
    {
        $carrito = session('carrito', []);

        $carrito[$producto->id] = [
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
            'cantidad' => ($carrito[$producto->id]['cantidad'] ?? 0) + 1,
        ];

        session(['carrito' => $carrito]);

        return back()->with('exito', "{$producto->nombre} agregado al pedido");
    }

    public function actualizarCantidad(Request $request, Producto $producto): RedirectResponse
    {
        $delta = (int) $request->input('delta', 0);
        $carrito = session('carrito', []);

        if (isset($carrito[$producto->id])) {
            $carrito[$producto->id]['cantidad'] += $delta;
            if ($carrito[$producto->id]['cantidad'] <= 0) {
                unset($carrito[$producto->id]);
            }
        }

        session(['carrito' => $carrito]);

        return back();
    }

    public function quitar(Producto $producto): RedirectResponse
    {
        $carrito = session('carrito', []);
        unset($carrito[$producto->id]);
        session(['carrito' => $carrito]);

        return back();
    }
}
