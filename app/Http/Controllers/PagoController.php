<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarPagoRequest;
use App\Models\CuentaCobro;
use App\Services\PagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Historia de usuario: HU-10 — Pago del pedido mediante código QR.
 * Controlador delgado: toda la lógica de negocio vive en PagoService.
 */
class PagoController extends Controller
{
    public function __construct(private PagoService $pagoService)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('menu.index')->with('error', 'Tu carrito está vacío. Agrega productos primero.');
        }

        $total = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);

        return view('pagos.show', [
            'carrito' => $carrito,
            'total' => $total,
            'cuenta' => CuentaCobro::first(),
        ]);
    }

    /**
     * Confirma el pago del carrito en sesión y genera el pedido con su factura.
     */
    public function store(ConfirmarPagoRequest $request): RedirectResponse
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('menu.index')->with('error', 'Tu carrito está vacío.');
        }

        // TODO: reemplazar por el polling/webhook real de la pasarela de pago;
        // por ahora el pago se confirma de inmediato (ver HU-10).
        $pago = $this->pagoService->confirmar($request->user(), $carrito, CuentaCobro::first());

        session()->forget('carrito');

        return redirect()
            ->route('pedidos.confirmacion', $pago->pedido)
            ->with('exito', 'Pago verificado correctamente ✓');
    }

    /** Cancela el pedido en curso y vuelve al menú, sin registrar ningún cobro. */
    public function cancelar(): RedirectResponse
    {
        return redirect()->route('menu.index')->with('exito', 'Pedido cancelado. No se realizó ningún cobro.');
    }

    /** Simula un pago rechazado por la pasarela, para pruebas de la interfaz. */
    public function error(): View
    {
        return view('pagos.error');
    }

    public function actualizarCuentaCobro(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'banco' => ['required', 'string', 'max:80'],
            'titular' => ['required', 'string', 'max:100'],
            'numero' => ['required', 'string', 'max:40'],
        ]);

        CuentaCobro::first()->update($datos);

        return back()->with('exito', 'Cuenta que recibe el pago actualizada.');
    }
}
