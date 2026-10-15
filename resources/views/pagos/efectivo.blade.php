@extends('layouts.app')

@section('titulo', 'Pagar en efectivo')
@section('ancho', 'angosto')

@section('contenido')
    <div class="pasos-pago">
        <span class="hecho">1 · Pedido</span>
        <span class="activo">2 · Pago en efectivo</span>
        <span>3 · Listo</span>
    </div>

    <section class="tarjeta centro">
        <h1>Pago en efectivo</h1>
        <p class="subtitulo">Pedido #{{ $pedido->id }}</p>
        <div class="monto-pago">Bs {{ number_format($pago->monto, 2) }}</div>

        @if ($pago->estado === 'fallido')
            <div class="alerta">La cafetería no pudo registrar este cobro. Puedes volver a intentarlo o elegir otra forma de pago.</div>
            <form method="POST" action="{{ route('pago.reintentar', $pago) }}">
                @csrf
                <button type="submit" class="btn">Intentar de nuevo</button>
            </form>
        @else
            <div class="aviso" style="text-align:left">
                <strong>¿Cómo sigue?</strong>
                <ol style="margin:8px 0 0;padding-left:20px">
                    <li>Ve a la caja de la cafetería.</li>
                    <li>Dile al cajero tu nombre o el número de pedido: <strong>#{{ $pedido->id }}</strong>.</li>
                    <li>Paga <strong>Bs {{ number_format($pago->monto, 2) }}</strong>. El cajero confirma tu cobro.</li>
                    <li>Esta pantalla cambia sola y recibes tu <strong>código de recojo</strong> y tu factura.</li>
                </ol>
            </div>
            <div class="espera"><span class="girar"></span> Esperando que la cafetería confirme tu cobro…</div>
        @endif
    </section>

    <section class="tarjeta">
        <h2>Tu pedido</h2>
        @foreach ($pedido->detalles as $detalle)
            <div class="resumen-linea"><span>{{ $detalle->cantidad }} × {{ $detalle->nombre }}</span><span>Bs {{ number_format($detalle->subtotal, 2) }}</span></div>
        @endforeach

        <details class="cancelar-pedido" style="margin-top:14px">
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
@endsection

@push('scripts')
<script>
    (function () {
        var estadoInicial = @json($pago->estado);
        var urlEstado = @json(route('pago.estado', $pago));

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
        if (estadoInicial === 'por_cobrar') { setInterval(consultar, 3000); }
    })();
</script>
@endpush
