<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\View\View;

/**
 * Panel de administración: una única cuenta (admin@cafeqr.edu) ve todos
 * los pedidos y pagos de todos los estudiantes, con estadísticas generales.
 */
class AdminController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with('detalle', 'pago', 'usuario', 'estado')
            ->latest()
            ->get();

        $totalVentas = $pedidos->sum('total');
        $totalProductos = $pedidos->flatMap->detalle->sum('cantidad');

        $masVendido = $pedidos->flatMap->detalle
            ->groupBy('nombre_producto')
            ->map(fn ($grupo) => $grupo->sum('cantidad'))
            ->sortDesc()
            ->keys()
            ->first();

        return view('admin.index', [
            'pedidos' => $pedidos,
            'totalVentas' => $totalVentas,
            'totalProductos' => $totalProductos,
            'masVendido' => $masVendido ?? '—',
        ]);
    }
}
