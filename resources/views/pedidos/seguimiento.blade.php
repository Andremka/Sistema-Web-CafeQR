@extends('layouts.app')

@section('titulo', 'Seguimiento del pedido')
@section('ancho', 'angosto')

@section('contenido')
    @php
        $orden = ['pagado' => 1, 'en_preparacion' => 2, 'listo' => 3, 'entregado' => 4];
        $nivel = $orden[$pedido->estado] ?? 0;
        $minutos = $pedido->minutosRestantes();
    @endphp

    <div class="cabecera-pagina">
        <div>
            <h1>Pedido #{{ $pedido->id }}</h1>
            <p class="subtitulo" style="margin:0">Código de recojo: <span class="codigo">{{ $pedido->codigo }}</span></p>
        </div>
        <span class="etiqueta estado-{{ $pedido->estado }}" id="etiqueta-estado">{{ $pedido->estado_texto }}</span>
    </div>

    <section class="tarjeta">
        @if ($pedido->estado === 'cancelado')
            <div class="tiempo-caja cancelado">
                <strong>Este pedido fue cancelado.</strong>
                <p style="margin:6px 0 0">
                    Cancelado por {{ $pedido->cancelado_por === 'cliente' ? 'ti' : 'la cafetería' }}@if ($pedido->motivo_cancelacion): “{{ $pedido->motivo_cancelacion }}”@endif.
                    @if ($pedido->reembolso_pendiente) Pasa por la cafetería para que te devuelvan tu dinero. @endif
                </p>
            </div>
        @else
            <ol class="pasos" aria-label="Estado del pedido">
                <li class="paso {{ $nivel > 1 ? 'hecho' : ($nivel === 1 ? 'actual' : '') }}" data-n="1">Pagado</li>
                <li class="paso {{ $nivel > 2 ? 'hecho' : ($nivel === 2 ? 'actual' : '') }}" data-n="2">En preparación</li>
                <li class="paso {{ $nivel > 3 ? 'hecho' : ($nivel === 3 ? 'actual' : '') }}" data-n="3">Listo</li>
                <li class="paso {{ $nivel === 4 ? 'hecho' : '' }}" data-n="4">Entregado</li>
            </ol>

            @if ($pedido->estado === 'pagado')
                <div class="tiempo-caja">
                    <strong>Tu pago está confirmado.</strong>
                    <p style="margin:6px 0 0">La cafetería empezará a prepararlo pronto. Cuando lo haga, aquí verás cuántos minutos faltan y te llegará un correo.</p>
                </div>
            @elseif ($pedido->estado === 'en_preparacion')
                <div class="tiempo-caja" id="caja-tiempo">
                    <div>Tu pedido estará listo en aproximadamente</div>
                    <div class="minutos"><span id="minutos">{{ $minutos ?? $pedido->tiempo_estimado }}</span> min</div>
                    <div class="ayuda">Hora estimada: <strong id="hora-estimada">{{ $pedido->listo_estimado?->timezone(config('app.timezone'))->format('H:i') }}</strong></div>
                </div>
            @elseif ($pedido->estado === 'listo')
                <div class="tiempo-caja listo">
                    <div class="minutos">🎉 ¡Tu pedido está listo!</div>
                    <p style="margin:6px 0 0">Pasa a recogerlo mostrando el código <strong>{{ $pedido->codigo }}</strong>.</p>
                </div>
            @else
                <div class="tiempo-caja listo">
                    <div class="minutos">✅ Pedido entregado</div>
                    <p style="margin:6px 0 0">¡Que lo disfrutes!</p>
                </div>
            @endif
        @endif

        <p class="ayuda centro" id="aviso-auto">Esta pantalla se actualiza sola.</p>
    </section>

    <section class="tarjeta">
        <h2>Detalle</h2>
        <div class="tabla-wrap">
            <table>
                <thead><tr><th>Producto</th><th>Cant.</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($pedido->detalles as $detalle)
                        <tr><td>{{ $detalle->nombre }}</td><td>{{ $detalle->cantidad }}</td><td class="num">Bs {{ number_format($detalle->subtotal, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($pedido->descuento > 0)<p class="ayuda" style="text-align:right;margin:0">Cupón {{ $pedido->cupon }}: −Bs {{ number_format($pedido->descuento, 2) }}</p>@endif
        <p class="total">Total: Bs {{ number_format($pedido->total, 2) }}</p>

        <div class="acciones">
            @if ($pedido->factura)
                <a class="btn contorno" href="{{ route('factura.show', $pedido) }}">Ver factura</a>
            @endif
            <a class="btn gris" href="{{ route('pedidos.historial') }}">Mis pedidos</a>
        </div>
    </section>

    @if ($pedido->puedeCancelarCliente())
        <section class="tarjeta">
            <details class="cancelar-pedido">
                <summary>¿Quieres cancelar este pedido?</summary>
                <p class="ayuda">Solo puedes cancelar mientras no haya empezado a prepararse. Si ya pagaste, te devolverán el dinero en la cafetería.</p>
                <form method="POST" action="{{ route('pedido.cancelar', $pedido) }}">
                    @csrf
                    <div class="campo">
                        <label for="motivo">Motivo <span class="opcional">(opcional)</span></label>
                        <textarea id="motivo" name="motivo" maxlength="200" placeholder="Cuéntanos por qué lo cancelas"></textarea>
                    </div>
                    <button type="submit" class="btn rojo">Sí, cancelar pedido</button>
                </form>
            </details>
        </section>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var estadoActual = @json($pedido->estado);
        var url = @json(route('pedido.estado', $pedido));
        var minutos = document.getElementById('minutos');
        var hora = document.getElementById('hora-estimada');

        function consultar() {
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d) { return; }
                    if (d.estado !== estadoActual) {
                        window.location.reload();
                        return;
                    }
                    if (minutos && d.minutos_restantes !== null) { minutos.textContent = d.minutos_restantes; }
                    if (hora && d.listo_estimado) { hora.textContent = d.listo_estimado; }
                })
                .catch(function () {});
        }

        if (estadoActual !== 'entregado' && estadoActual !== 'cancelado') {
            setInterval(consultar, 5000);
        } else {
            var aviso = document.getElementById('aviso-auto');
            if (aviso) { aviso.style.display = 'none'; }
        }
    })();
</script>
@endpush
