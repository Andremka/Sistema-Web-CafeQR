<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar pago · Banco demo</title>
    <link rel="stylesheet" href="{{ asset('css/cafeqr.css') }}">
</head>
<body class="fondo-auth">
    <div class="auth-envoltorio">
        <div class="caja centro">
            <div class="caja-marca" style="justify-content:center">
                @include('partials.logo-cafeqr', ['tam' => 40])
                <span>Banco demo CafeQR</span>
            </div>

            @if ($pago->estado === 'pendiente')
                <h1>Confirmar pago</h1>
                <p class="subtitulo">Pagas a <strong>{{ config('cafeqr.emisor.nombre') }}</strong></p>
                <div class="monto-pago">Bs {{ number_format($pago->monto, 2) }}</div>
                <p class="ayuda">Pedido #{{ $pedido->id }} · {{ $pedido->user->name }}</p>

                <form method="POST" action="{{ route('pagar.ejecutar', $pago->referencia) }}">
                    @csrf
                    <button type="submit" class="enviar">Pagar Bs {{ number_format($pago->monto, 2) }}</button>
                </form>
                <p class="ayuda" style="margin-top:12px">Pantalla de demostración: no se cobra dinero real.</p>
            @elseif (in_array($pago->estado, ['recibido', 'completado']))
                <div class="marca-ok">✓</div>
                <h1>¡Pago realizado!</h1>
                <p class="subtitulo">Vuelve a la computadora: ahí te pedimos el código de 6 dígitos que te llegó al correo para terminar tu compra.</p>
            @elseif ($pago->estado === 'fallido')
                <h1>QR vencido</h1>
                <p class="subtitulo">Este código ya no es válido. Genera uno nuevo desde la pantalla de pago de tu pedido.</p>
            @else
                <h1>Pago no disponible</h1>
                <p class="subtitulo">Este pago ya no está activo.</p>
            @endif
        </div>
    </div>
</body>
</html>
