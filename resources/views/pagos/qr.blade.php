@extends('layouts.app')

@section('titulo', 'Pagar con QR')
@section('ancho', 'angosto')

@section('contenido')
    <div class="pasos-pago">
        <span class="hecho">1 · Pedido</span>
        <span class="activo">2 · Pago con QR</span>
        <span>3 · Código del correo</span>
        <span>4 · Listo</span>
    </div>

    <section class="tarjeta centro">
        <h1>Pago con QR</h1>
        <p class="subtitulo">Pedido #{{ $pedido->id }}</p>
        <div class="monto-pago">Bs {{ number_format($pago->monto, 2) }}</div>

        @if ($pago->estado === 'fallido')
            <div class="alerta">Este pago venció o fue rechazado. Genera uno nuevo para continuar.</div>
            <form method="POST" action="{{ route('pago.reintentar', $pago) }}">
                @csrf
                <button type="submit" class="btn">Generar nuevo QR</button>
            </form>

        @elseif ($modoBanco)
            {{-- ===== Modo banco: tu QR de Yape ===== --}}
            @if ($qrBancoDisponible)
                <div class="qr-caja"><img src="{{ asset(config('cafeqr.qr_cobro')) }}" alt="QR de {{ config('cafeqr.banco_nombre') }}"></div>
                <div class="resumen-banco">
                    Abre <strong>{{ config('cafeqr.banco_nombre') }}</strong>, toca <strong>Escanear QR</strong> y paga
                    <strong>Bs {{ number_format($pago->monto, 2) }}</strong>
                    @if (config('cafeqr.banco_titular')) a nombre de <strong>{{ config('cafeqr.banco_titular') }}</strong>@endif.
                </div>
                <p class="ayuda">Este QR se escanea desde la app de {{ config('cafeqr.banco_nombre') }}, no con la cámara normal.</p>
            @else
                <div class="alerta">No se encontró la imagen del QR de cobro. Copia tu QR en <code>public/{{ config('cafeqr.qr_cobro') }}</code> o cambia a <code>PAGO_MODO=demo</code> en el .env.</div>
            @endif

            @if ($pago->estado === 'pendiente')
                <form method="POST" action="{{ route('pago.ya-pague', $pago) }}">
                    @csrf
                    <button type="submit" class="btn block" @disabled(! $qrBancoDisponible)>Ya pagué</button>
                </form>
                <p class="cuenta-regresiva" style="margin-top:12px">⏳ Vence en <span id="cuenta">--:--</span></p>
            @else
                <div class="espera"><span class="girar"></span> Estamos verificando tu pago. Cuando la cafetería lo confirme, te llegará un código a tu correo.</div>
            @endif

        @elseif ($pago->estado === 'pendiente' && $qr)
            {{-- ===== Modo demo: QR que abre la "app del banco" de prueba ===== --}}
            <div class="qr-caja"><img src="{{ $qr }}" alt="QR de pago del pedido {{ $pedido->id }}"></div>
            <p class="ayuda">Escanéalo con la cámara de tu celular. Debe estar conectado al <strong>mismo Wi-Fi</strong> que esta computadora.</p>
            <p class="url-qr">{{ $urlQr }}</p>

            @if ($enLocalhost)
                <div class="aviso" style="text-align:left">
                    Abriste la página como <strong>localhost</strong>, así que el QR ya apunta a la IP de tu computadora para que el celular pueda abrirlo.
                    Si el celular no abre el enlace, inicia el servidor con <code>iniciar-cafeqr.bat</code> y permite el acceso en el Firewall de Windows.
                </div>
            @endif

            <div class="demo-box" style="text-align:left">
                <strong>🧪 Modo demostración</strong>
                <p>¿Sin celular a la mano? Simula el pago desde aquí:</p>
                <form method="POST" action="{{ route('pago.simular', $pago) }}">
                    @csrf
                    <button type="submit" class="btn verde">Simular pago exitoso</button>
                </form>
            </div>
            <p class="cuenta-regresiva">⏳ Vence en <span id="cuenta">--:--</span></p>
        @else
            <div class="espera"><span class="girar"></span> Procesando…</div>
        @endif
    </section>

    @if (! in_array($pago->estado, ['recibido', 'completado']))
        <section class="tarjeta">
            <details class="cancelar-pedido">
                <summary>Cancelar este pedido</summary>
                <form method="POST" action="{{ route('pedido.cancelar', $pedido) }}">
                    @csrf
                    <div class="campo">
                        <label for="motivo">Motivo <span class="opcional">(opcional)</span></label>
                        <textarea id="motivo" name="motivo" maxlength="200" placeholder="Cuéntanos por qué lo cancelas"></textarea>
                    </div>
                    <button type="submit" class="btn rojo">Sí, cancelar pedido</button>
                </form>
            </details>
            <p class="ayuda" style="margin:10px 0 0"><a href="{{ route('pedido.actual') }}">← Volver a mi pedido</a></p>
        </section>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var estadoInicial = @json($pago->estado);
        var urlEstado = @json(route('pago.estado', $pago));
        var vence = @json($pago->vence_en?->timestamp);
        var cuenta = document.getElementById('cuenta');

        function actualizarCuenta() {
            if (!cuenta || !vence) { return; }
            var falta = Math.max(0, vence - Math.floor(Date.now() / 1000));
            var m = String(Math.floor(falta / 60)).padStart(2, '0');
            var s = String(falta % 60).padStart(2, '0');
            cuenta.textContent = m + ':' + s;
            if (falta === 0 && estadoInicial === 'pendiente') { window.location.reload(); }
        }
        actualizarCuenta();
        setInterval(actualizarCuenta, 1000);

        function consultar() {
            fetch(urlEstado, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d) { return; }
                    if (d.siguiente) { window.location.href = d.siguiente; return; }
                    if (d.estado !== estadoInicial) { window.location.reload(); }
                })
                .catch(function () {});
        }
        if (['pendiente', 'por_verificar'].indexOf(estadoInicial) !== -1) {
            setInterval(consultar, 3000);
        }
    })();
</script>
@endpush
