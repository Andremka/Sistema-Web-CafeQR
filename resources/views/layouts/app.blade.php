<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Panel') · CafeQR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cafeqr.css') }}?v=6">
</head>
<body>
@php($u = auth()->user())

<header class="barra">
    <div class="barra-izq">
        <a class="marca" href="{{ route($u->rutaInicio()) }}">
            @include('partials.logo-cafeqr', ['tam' => 34])
            <span>CafeQR</span>
        </a>

        <nav class="nav-links" aria-label="Navegación principal">
            @if ($u->esCliente())
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'activo' : '' }}">Inicio</a>
                <a href="{{ route('pedidos.historial') }}" class="{{ request()->routeIs('pedidos.historial') ? 'activo' : '' }}">Mis pedidos</a>
            @else
                @if ($u->esAdministrador())
                    <a href="{{ route('admin.panel') }}" class="{{ request()->routeIs('admin.panel') ? 'activo' : '' }}">Panel</a>
                @endif
                <a href="{{ route('entregas.index') }}" class="{{ request()->routeIs('entregas.*') ? 'activo' : '' }}">Entregas</a>
                <a href="{{ route('caja.pagos') }}" class="{{ request()->routeIs('caja.*') ? 'activo' : '' }}">Caja</a>
                <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'activo' : '' }}">Productos</a>
                @if ($u->esAdministrador())
                    <a href="{{ route('admin.pedidos') }}" class="{{ request()->routeIs('admin.pedidos') ? 'activo' : '' }}">Pedidos</a>
                    <a href="{{ route('admin.facturas') }}" class="{{ request()->routeIs('admin.facturas') ? 'activo' : '' }}">Facturas</a>
                    <a href="{{ route('admin.usuarios') }}" class="{{ request()->routeIs('admin.usuarios*', 'admin.empleado.*') ? 'activo' : '' }}">Usuarios</a>
                @endif
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'activo' : '' }}">Ver menú</a>
            @endif
        </nav>

        @if ($u->esCliente())
            <a class="carrito-link" href="{{ route('pedido.actual') }}">🛒 Mi pedido @if (($itemsCarrito ?? 0) > 0)({{ $itemsCarrito }})@endif</a>
        @endif
    </div>

    <details class="perfil">
        <summary>
            <span class="rol-chip">{{ $u->rol_texto }}</span>
            <span class="nombre">{{ $u->name }}</span>
            @include('partials.avatar', ['user' => $u, 'tam' => 40])
        </summary>
        <div class="menu">
            <a href="{{ route('perfil.show') }}">👤 Mi perfil</a>
            @if ($u->esCliente())
                <a href="{{ route('pedidos.historial') }}">🧾 Mis pedidos</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="salir">Cerrar sesión</button>
            </form>
        </div>
    </details>
</header>

@if (! empty($pedidoListo))
    <div class="banner-listo">
        <span>🎉 ¡Tu pedido #{{ $pedidoListo->id }} está listo para recoger! Código: <strong>{{ $pedidoListo->codigo }}</strong></span>
        <a href="{{ route('pedido.seguimiento', $pedidoListo) }}">Ver detalles →</a>
    </div>
@endif

@php($conBarra = $u->esCliente() && request()->routeIs('dashboard') && ($itemsCarrito ?? 0) > 0)
<main class="contenedor @yield('ancho') {{ $conBarra ? 'con-barra-pedido' : '' }}">
    @include('partials.mensajes')
    @yield('contenido')
</main>

@if ($conBarra)
    <a class="barra-pedido" href="{{ route('pedido.actual') }}">
        <span class="bp-n">{{ $itemsCarrito }}</span>
        <span class="bp-txt">Tu pedido<small>{{ $itemsCarrito === 1 ? '1 producto' : $itemsCarrito.' productos' }}</small></span>
        <span class="bp-total">Bs {{ number_format($totalCarrito ?? 0, 2) }}</span>
        <span class="bp-go">Ver pedido</span>
    </a>
@endif

<footer class="pie-pagina">CafeQR · Universidad Privada del Valle · La Paz, Bolivia</footer>

@stack('scripts')
</body>
</html>
