@extends('layouts.app')

@section('titulo', 'Mis pedidos')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Mis pedidos</h1>
            <p class="subtitulo" style="margin:0">Todo lo que pediste, con su estado y su factura.</p>
        </div>
        <a class="btn" href="{{ route('dashboard') }}">+ Nuevo pedido</a>
    </div>

    @forelse ($pedidos as $pedido)
        <article class="pedido-card {{ $pedido->estado === 'listo' ? 'listo' : '' }}">
            <div class="pedido-cab">
                <strong>Pedido #{{ $pedido->id }}</strong>
                <span class="etiqueta estado-{{ $pedido->estado }}">{{ $pedido->estado_texto }}</span>
            </div>
            <p class="ayuda" style="margin:4px 0 0">
                {{ $pedido->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @if ($pedido->metodo_pago) · {{ $pedido->metodo_pago === 'qr' ? 'Pago con QR' : 'Efectivo' }} @endif
                @if ($pedido->codigo) · Código de recojo: <span class="codigo">{{ $pedido->codigo }}</span> @endif
            </p>
            <ul class="pedido-items">
                @foreach ($pedido->detalles as $detalle)
                    <li>{{ $detalle->cantidad }} × {{ $detalle->nombre }}</li>
                @endforeach
            </ul>
            @if ($pedido->estado === 'en_preparacion' && $pedido->minutosRestantes() !== null)
                <p class="ayuda">⏱️ Listo en aprox. {{ $pedido->minutosRestantes() }} min</p>
            @endif
            @if ($pedido->estado === 'cancelado')
                <p class="ayuda">Cancelado por {{ $pedido->cancelado_por === 'cliente' ? 'ti' : 'la cafetería' }}@if ($pedido->motivo_cancelacion): “{{ $pedido->motivo_cancelacion }}”@endif.
                    @if ($pedido->reembolso_pendiente) <strong>Reembolso pendiente: pasa por la cafetería.</strong> @elseif ($pedido->reembolsado_en) Reembolsado. @endif
                </p>
            @endif

            <div class="pedido-acciones">
                <strong style="color:var(--guinda)">Bs {{ number_format($pedido->total, 2) }}</strong>
                @if ($pedido->estado === 'pendiente_pago')
                    <a class="btn chico" href="{{ route('pedido.continuar') }}">Continuar con el pago</a>
                @elseif (in_array($pedido->estado, ['pagado', 'en_preparacion', 'listo', 'entregado']))
                    <a class="btn chico" href="{{ route('pedido.seguimiento', $pedido) }}">Seguir mi pedido</a>
                @endif
                @if ($pedido->factura)
                    <a class="btn chico contorno" href="{{ route('factura.show', $pedido) }}">Ver factura</a>
                @endif
            </div>
        </article>
    @empty
        <div class="tarjeta vacio">
            <span class="grande">🧾</span>
            Todavía no hiciste ningún pedido.<br>
            <a class="btn" href="{{ route('dashboard') }}">Ver el menú</a>
        </div>
    @endforelse

    {{ $pedidos->links() }}
@endsection
