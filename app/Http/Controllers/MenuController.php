<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Historia de usuario: HU-05 — Consultar menú disponible.
     * Lista el catálogo agrupado por categoría, ordenado por precio,
     * con búsqueda opcional por nombre de producto.
     */
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));

        $categorias = Categoria::with(['productos' => function ($query) use ($busqueda) {
            $query->disponibles()->orderBy('precio');
            if ($busqueda !== '') {
                $query->where('nombre', 'like', "%{$busqueda}%");
            }
        }])->orderBy('orden')->get()->filter(fn ($categoria) => $categoria->productos->isNotEmpty());

        return view('menu.index', [
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'carrito' => session('carrito', []),
        ]);
    }
}
