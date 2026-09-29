<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('titulo', 'CafeQR')</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

@auth
<header class="header">
    <div class="logo">☕ CafeQR</div>
    <nav class="nav">
        @if (auth()->user()->esAdministrador())
            <a href="{{ route('admin.index') }}">Panel Admin</a>
        @else
            <a href="{{ route('menu.index') }}">Menú</a>
            <a href="{{ route('pagos.show') }}">Pago</a>
        @endif

        <div class="user-menu-wrapper">
            <button type="button" class="user-tag-btn" onclick="toggleUserMenu(event)">
                <span class="avatar-circle-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                {{ Str::before(auth()->user()->name, ' ') }} ▾
            </button>

            <div class="dropdown-menu" id="userDropdown">
                <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <strong>{{ auth()->user()->name }}</strong>
                <p class="email">{{ auth()->user()->email }}</p>

                @unless (auth()->user()->esAdministrador())
                    <div class="cupo-box">
                        <span>Cupo disponible</span>
                        <strong>Bs {{ number_format(session('cupo_disponible', 50), 0) }}</strong>
                    </div>
                    <form action="{{ route('cupones.canjear') }}" method="POST" class="cupo-form">
                        @csrf
                        <input type="text" name="codigo" placeholder="Código de cupón" maxlength="14">
                        <button type="submit">Canjear</button>
                    </form>
                    <hr>
                    <a href="{{ route('pedidos.mios') }}">📦 Mis pedidos</a>
                @endunless

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">🚪 Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>
</header>
<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('open');
}
document.addEventListener('click', function (e) {
    var menu = document.getElementById('userDropdown');
    if (menu && !e.target.closest('.user-menu-wrapper')) {
        menu.classList.remove('open');
    }
});
</script>
@endauth

<div class="wrap">
    @if (session('exito'))
        <div class="alert alert-success">✓ {{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">⚠ {{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @yield('contenido')
</div>

<div class="footer">CafeQR · Proyecto de Sistemas I · Universidad Privada del Valle · Equipo "Los Suchas"</div>

</body>
</html>
