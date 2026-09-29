@extends('layouts.app')
@section('titulo', 'Pago con código QR · CafeQR')
@section('contenido')
<p><a href="{{ route('menu.index') }}">← Volver al menú</a></p>

<div class="card card-narrow" style="text-align:center;">
    <h2>Pago con código QR</h2>
    <p style="color:var(--ink-500);">Escanea el código con tu app bancaria o billetera móvil</p>

    <div style="text-align:left; background:var(--guindo-50); border-radius:9px; padding:12px 14px; margin-bottom:6px;">
        <span style="font-size:11.5px; font-weight:600; color:var(--guindo-700); text-transform:uppercase;">Cuenta que recibe el pago</span>
        <p style="margin:3px 0 10px; font-size:13px;">{{ $cuenta->banco }} · {{ $cuenta->titular }} · Nro {{ $cuenta->numero }}</p>

        <details>
            <summary class="btn-link" style="cursor:pointer;">Editar</summary>
            <form action="{{ route('cuenta-cobro.actualizar') }}" method="POST" style="margin-top:10px;">
                @csrf @method('PATCH')
                <div class="field"><label>Banco</label><input type="text" name="banco" value="{{ $cuenta->banco }}"></div>
                <div class="field"><label>Titular de la cuenta</label><input type="text" name="titular" value="{{ $cuenta->titular }}"></div>
                <div class="field"><label>Número de cuenta</label><input type="text" name="numero" value="{{ $cuenta->numero }}"></div>
                <button type="submit" class="btn">Guardar cuenta</button>
            </form>
        </details>
    </div>

    <div class="qr-box">
        {{-- Requiere el paquete simplesoftwareio/simple-qrcode (ver README-LARAVEL.md) --}}
        {!! QrCode::size(200)->generate('CafeQR|Bs ' . number_format($total, 2) . '|' . now()->timestamp) !!}
    </div>
    <p style="font-size:12.5px; color:var(--ink-500);">El código expira en 10 minutos</p>
    <p>Total a pagar: <strong style="color:var(--guindo-800); font-size:19px;">Bs {{ number_format($total, 2) }}</strong></p>

    <form action="{{ route('pagos.store') }}" method="POST" id="form-pago">
        @csrf
        <button type="submit" class="btn" id="btn-pagar">Pagar ahora</button>
    </form>
    <form action="{{ route('pagos.cancelar') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-secondary">Cancelar pedido</button>
    </form>
    <a href="{{ route('pagos.error') }}" class="btn btn-error">Simular: Error de pago</a>
</div>

<script>
    // Pequeño detalle de UX: evita doble clic mientras se procesa el pago.
    document.getElementById('form-pago').addEventListener('submit', function () {
        document.getElementById('btn-pagar').disabled = true;
        document.getElementById('btn-pagar').textContent = 'Verificando el pago…';
    });
</script>
@endsection
