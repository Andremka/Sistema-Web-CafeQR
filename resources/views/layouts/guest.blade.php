<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Bienvenido') · CafeQR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cafeqr.css') }}?v=6">
</head>
<body class="fondo-auth">
    <div class="auth-split">
        <aside class="auth-lado">
            <div class="auth-lado-top">
                @include('partials.logo-univalle')
            </div>
            <div class="auth-lado-medio">
                <h2>Tu café, sin hacer fila.</h2>
                <p>Pide desde tu celular, paga con QR o efectivo y recoge con un código.</p>
            </div>
            <ul class="auth-puntos">
                <li><span class="ic">📱</span><span><strong>Paga con QR</strong>Escanea y confirma con el código que te llega al correo.</span></li>
                <li><span class="ic">🔔</span><span><strong>Te avisamos</strong>Correo en cada paso: preparación y pedido listo.</span></li>
                <li><span class="ic">🧾</span><span><strong>Recoge sin fila</strong>Con tu código de recojo y tu factura incluida.</span></li>
            </ul>
        </aside>
        <div class="caja">
            <div class="caja-marca">
                @include('partials.logo-cafeqr', ['tam' => 38])
                <span>CafeQR</span>
            </div>
            @include('partials.mensajes')
            @yield('contenido')
        </div>
    </div>
    @stack('scripts')
</body>
</html>
