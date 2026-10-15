<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q'));

        $consulta = Producto::query()->orderBy('nombre');
        if ($buscar !== '') {
            $consulta->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('descripcion', 'like', '%'.$buscar.'%');
            });
        }

        $porCategoria = $consulta->get()->groupBy('categoria');

        // Orden fijo de categorías; las que no estén en la lista van al final
        $ordenadas = [];
        foreach (array_keys(Producto::CATEGORIAS) as $cat) {
            if ($porCategoria->has($cat)) {
                $ordenadas[$cat] = $porCategoria[$cat];
            }
        }
        foreach ($porCategoria as $cat => $items) {
            $ordenadas[$cat] ??= $items;
        }

        return view('menu.dashboard', ['categorias' => $ordenadas, 'buscar' => $buscar]);
    }
}
