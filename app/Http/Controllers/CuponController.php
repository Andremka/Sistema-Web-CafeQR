<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cupones de saldo (UNIVALLE10, CAFEQR20) canjeables por el estudiante.
 * El cupo disponible se guarda en sesión: es un saldo promocional, no un
 * método de pago real, por lo que no requiere su propia tabla de movimientos.
 */
class CuponController extends Controller
{
    public function canjear(Request $request): RedirectResponse
    {
        $request->validate(['codigo' => ['required', 'string', 'max:20']]);

        $codigo = strtoupper(trim($request->input('codigo')));
        $cupon = Cupon::where('codigo', $codigo)->first();

        if (! $cupon) {
            return back()->with('error', 'Código de cupón no válido.');
        }

        $cupoActual = session('cupo_disponible', 50);
        session(['cupo_disponible' => $cupoActual + (float) $cupon->monto]);

        return back()->with('exito', 'Cupón aplicado: +Bs '.number_format($cupon->monto, 0));
    }
}
