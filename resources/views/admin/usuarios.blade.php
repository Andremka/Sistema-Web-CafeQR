@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Usuarios y empleados</h1>
            <p class="subtitulo" style="margin:0">Los clientes se registran solos. Aquí creas a los empleados, que gestionan productos, caja y entregas.</p>
        </div>
        <a class="btn" href="{{ route('admin.empleado.crear') }}">+ Nuevo empleado</a>
    </div>

    <section class="tarjeta">
        <form class="filtros" method="GET" action="{{ route('admin.usuarios') }}">
            <div class="campo">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $buscar }}" placeholder="Nombre o correo" maxlength="60">
            </div>
            <div class="campo">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="">Todos</option>
                    @foreach (\App\Models\User::ROLES as $clave => $texto)
                        <option value="{{ $clave }}" {{ $rol === $clave ? 'selected' : '' }}>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn">Filtrar</button>
        </form>

        <div class="tabla-wrap">
            <table>
                <thead><tr><th>Usuario</th><th>Rol</th><th>Pedidos</th><th>Desde</th><th>Acciones</th></tr></thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    @include('partials.avatar', ['user' => $usuario, 'tam' => 36])
                                    <div><strong>{{ $usuario->name }}</strong><br><span class="ayuda">{{ $usuario->email }}{{ $usuario->telefono ? ' · '.$usuario->telefono : '' }}</span></div>
                                </div>
                            </td>
                            <td><span class="etiqueta rol-{{ $usuario->rol }}">{{ $usuario->rol_texto }}</span></td>
                            <td>{{ $usuario->pedidos_hechos }}</td>
                            <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if ($usuario->esAdministrador() || $usuario->id === auth()->id())
                                    <span class="ayuda">—</span>
                                @else
                                    <div class="acciones-fila">
                                        <form method="POST" action="{{ route('admin.usuarios.rol', $usuario) }}">
                                            @csrf
                                            <input type="hidden" name="rol" value="{{ $usuario->esEmpleado() ? 'cliente' : 'empleado' }}">
                                            <button type="submit" class="btn chico gris">{{ $usuario->esEmpleado() ? 'Pasar a cliente' : 'Hacer empleado' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.usuarios.eliminar', $usuario) }}" onsubmit="return confirm('¿Eliminar a {{ addslashes($usuario->name) }}?{{ $usuario->pedidos_hechos ? ' Tiene '.$usuario->pedidos_hechos.' pedido(s): también se borrarán sus pedidos y facturas. No se puede deshacer.' : '' }}');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn chico rojo">Eliminar</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $usuarios->links() }}
    </section>
@endsection
