<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Gestión de productos: empleados y administrador. */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('q'));
        $categoria = $request->query('categoria');
        $estado = $request->query('estado');

        $productos = Producto::query()
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', '%'.$buscar.'%'))
            ->when($categoria && $categoria !== 'todas', fn ($q) => $q->where('categoria', $categoria))
            ->when($estado === 'disponibles', fn ($q) => $q->where('disponible', true))
            ->when($estado === 'agotados', fn ($q) => $q->where('disponible', false))
            ->orderBy('categoria')->orderBy('nombre')
            ->paginate(12)->withQueryString();

        return view('productos.index', [
            'productos' => $productos,
            'buscar' => $buscar,
            'categoria' => $categoria,
            'estado' => $estado,
        ]);
    }

    public function create(): View
    {
        return view('productos.form', ['producto' => new Producto(['disponible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $datos['imagen'] = $this->guardarImagen($request);
        Producto::create($datos);

        return redirect()->route('productos.index')->with('exito', 'Producto agregado al menú.');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.form', ['producto' => $producto]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $this->validar($request, $producto);

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($producto);
            $datos['imagen'] = $this->guardarImagen($request);
        } elseif ($request->boolean('quitar_imagen')) {
            $this->borrarImagen($producto);
            $datos['imagen'] = null;
        } else {
            unset($datos['imagen']);
        }

        $producto->update($datos);

        return redirect()->route('productos.index')->with('exito', 'Producto actualizado.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $nombre = $producto->nombre;
        $this->borrarImagen($producto);
        $producto->delete();

        return redirect()->route('productos.index')->with('exito', '"'.$nombre.'" se quitó del menú. Los pedidos anteriores conservan su registro.');
    }

    /** Marcar disponible / agotado con un clic. */
    public function alternar(Producto $producto): RedirectResponse
    {
        $producto->update(['disponible' => ! $producto->disponible]);

        return back()->with('exito', $producto->nombre.' ahora está '.($producto->disponible ? 'disponible.' : 'agotado.'));
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        $request->merge([
            'nombre' => preg_replace('/\s+/u', ' ', trim((string) $request->input('nombre'))),
            'precio' => str_replace(',', '.', trim((string) $request->input('precio'))),
        ]);

        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'min:3', 'max:60',
                "regex:/^[\\pL\\d][\\pL\\d\\s().,&'+-]*$/u",
                Rule::unique('productos', 'nombre')->ignore($producto?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'precio' => ['required', 'numeric', 'min:0.5', 'max:999.99', 'decimal:0,2'],
            'categoria' => ['required', Rule::in(array_keys(Producto::CATEGORIAS))],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede pasar de 60 caracteres.',
            'nombre.regex' => 'El nombre tiene caracteres no permitidos.',
            'nombre.unique' => 'Ya existe un producto con ese nombre.',
            'descripcion.max' => 'La descripción no puede pasar de 200 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número (ejemplo: 12.50).',
            'precio.min' => 'El precio mínimo es Bs 0.50.',
            'precio.max' => 'El precio máximo es Bs 999.99.',
            'precio.decimal' => 'El precio puede tener como máximo 2 decimales.',
            'categoria.required' => 'Elige una categoría.',
            'categoria.in' => 'La categoría elegida no es válida.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $datos['disponible'] = $request->boolean('disponible');
        $datos['descripcion'] = ($datos['descripcion'] ?? null) ?: null;

        return $datos;
    }

    private function guardarImagen(Request $request): ?string
    {
        if (! $request->hasFile('imagen')) {
            return null;
        }

        $carpeta = public_path('productos');
        if (! is_dir($carpeta)) {
            mkdir($carpeta, 0775, true);
        }

        $archivo = $request->file('imagen');
        $nombre = 'p'.time().'_'.bin2hex(random_bytes(3)).'.'.$archivo->getClientOriginalExtension();
        $archivo->move($carpeta, $nombre);

        return $nombre;
    }

    private function borrarImagen(Producto $producto): void
    {
        if ($producto->imagen) {
            $ruta = public_path('productos'.DIRECTORY_SEPARATOR.$producto->imagen);
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
    }
}
