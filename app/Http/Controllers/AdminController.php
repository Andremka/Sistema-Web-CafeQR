<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\Notificador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Solo administrador. */
class AdminController extends Controller
{
    public function panel(): View
    {
        $vendidosHoy = Pedido::whereNotNull('pagado_en')->whereDate('pagado_en', today())
            ->where('estado', '!=', Pedido::CANCELADO);

        return view('admin.panel', [
            'ventasHoy' => (float) (clone $vendidosHoy)->sum('total'),
            'pedidosHoy' => (clone $vendidosHoy)->count(),
            'porVerificar' => Pago::whereIn('estado', ['por_verificar', 'por_cobrar'])->count(),
            'porEstado' => Pedido::query()->whereIn('estado', Pedido::ACTIVOS)
                ->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'reembolsos' => Pedido::where('reembolso_pendiente', true)->count(),
            'clientes' => User::where('rol', User::ROL_CLIENTE)->count(),
            'empleados' => User::where('rol', User::ROL_EMPLEADO)->count(),
            'productos' => Producto::count(),
            'agotados' => Producto::where('disponible', false)->count(),
            'facturas' => Factura::count(),
            'totalFacturado' => (float) Pedido::whereHas('factura')->where('estado', '!=', Pedido::CANCELADO)->sum('total'),
            'modoPrueba' => Notificador::modoPrueba(),
            'modoPago' => config('cafeqr.pago_modo'),
        ]);
    }

    public function pedidos(Request $request): View
    {
        $estado = $request->query('estado');
        $buscar = trim((string) $request->query('q'));

        $pedidos = Pedido::query()
            ->where('estado', '!=', Pedido::CARRITO)
            ->with(['user', 'detalles'])
            ->when($estado && $estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($q2) use ($buscar) {
                    $q2->where('codigo', 'like', '%'.strtoupper($buscar).'%')
                        ->orWhere('id', ctype_digit($buscar) ? (int) $buscar : 0)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$buscar.'%')->orWhere('email', 'like', '%'.$buscar.'%'));
                });
            })
            ->latest('id')->paginate(15)->withQueryString();

        return view('admin.pedidos', ['pedidos' => $pedidos, 'estado' => $estado, 'buscar' => $buscar]);
    }

    public function facturas(Request $request): View
    {
        $buscar = trim((string) $request->query('q'));

        $facturas = Factura::query()
            ->with(['pedido.user'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($q2) use ($buscar) {
                    $q2->where('numero', ctype_digit($buscar) ? (int) $buscar : 0)
                        ->orWhereHas('pedido', fn ($p) => $p->where('razon_social', 'like', '%'.$buscar.'%')
                            ->orWhere('nit_ci', 'like', '%'.$buscar.'%')
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$buscar.'%')));
                });
            })
            ->latest('numero')->paginate(15)->withQueryString();

        return view('admin.facturas', ['facturas' => $facturas, 'buscar' => $buscar]);
    }

    public function usuarios(Request $request): View
    {
        $rol = $request->query('rol');
        $buscar = trim((string) $request->query('q'));

        $usuarios = User::query()
            ->when($rol && array_key_exists($rol, User::ROLES), fn ($q) => $q->where('rol', $rol))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', '%'.$buscar.'%')->orWhere('email', 'like', '%'.$buscar.'%')))
            ->withCount(['pedidos as pedidos_hechos' => fn ($q) => $q->where('estado', '!=', Pedido::CARRITO)])
            ->orderByRaw("CASE rol WHEN 'administrador' THEN 0 WHEN 'empleado' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('admin.usuarios', ['usuarios' => $usuarios, 'rol' => $rol, 'buscar' => $buscar]);
    }

    public function crearEmpleado(): View
    {
        return view('admin.empleado-form');
    }

    public function guardarEmpleado(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $request->input('name'))),
            'email' => strtolower(trim((string) $request->input('email'))),
            'telefono' => preg_replace('/[\s-]+/', '', (string) $request->input('telefono')) ?: null,
        ]);

        $datos = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60', "regex:/^\\pL+(?:[\\s.'-]+\\pL+)*\\.?$/u"],
            'email' => ['required', 'string', 'email:rfc', 'max:120', 'unique:users,email'],
            'telefono' => ['nullable', 'regex:/^[67]\d{7}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 letras.',
            'name.regex' => 'El nombre solo puede tener letras y espacios (sin números ni símbolos).',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Escribe un correo válido.',
            'email.unique' => 'Ese correo ya está registrado.',
            'telefono.regex' => 'El celular debe tener 8 dígitos y empezar con 6 o 7.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ]);

        User::create($datos + ['rol' => User::ROL_EMPLEADO]);

        return redirect()->route('admin.usuarios')
            ->with('exito', 'Empleado creado. Entrégale su correo y su contraseña para que inicie sesión.');
    }

    public function cambiarRol(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id || $usuario->esAdministrador()) {
            return back()->with('error', 'No puedes cambiar el rol del administrador.');
        }

        $datos = $request->validate([
            'rol' => ['required', Rule::in([User::ROL_CLIENTE, User::ROL_EMPLEADO])],
        ], ['rol.in' => 'El rol elegido no es válido.', 'rol.required' => 'Elige un rol.']);

        $usuario->update(['rol' => $datos['rol']]);

        return back()->with('exito', $usuario->name.' ahora es '.$usuario->rol_texto.'.');
    }

    public function eliminar(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id || $usuario->esAdministrador()) {
            return back()->with('error', 'No puedes eliminar al administrador.');
        }

        $nombre = $usuario->name;
        $pedidos = $usuario->pedidos()->where('estado', '!=', Pedido::CARRITO)->count();

        // Se borran también sus pedidos, pagos y facturas (cascada en la base de datos).
        $usuario->delete();

        return back()->with('exito', 'Eliminaste a '.$nombre.($pedidos ? ' y sus '.$pedidos.' pedido(s) con sus facturas' : '').'. Ya puede volver a registrarse.');
    }
}
