@extends('layouts.app')
@section('titulo', 'Mis pedidos · CafeQR')
@section('contenido')
<p><a href="{{ route('menu.index') }}">← Volver al menú</a></p>

<div class="card card-mid">
    <h2>Mis pedidos</h2>

    @if ($pedidos->isEmpty())
        <p style="color:var(--ink-500);">Aún no tienes pedidos realizados.</p>
    @else
        <div style="display:flex; justify-content:space-between; background:var(--guindo-50); border-radius:9px; padding:10px 14px; margin:14px 0; font-size:13px;">
            <span>{{ $pedidos->count() }} pedido{{ $pedidos->count() > 1 ? 's' : '' }}</span>
            <strong style="color:var(--guindo-700);">Total gastado: Bs {{ number_format($totalGastado, 2) }}</strong>
        </div>

        @foreach ($pedidos as $pedido)
            <div class="order-entry">
                <div class="order-top">
                    <strong style="color:var(--guindo-700);">Pedido #{{ $pedido->numero_pedido }}</strong>
                    <span>{{ $pedido->created_at->format('d/m/Y · h:i A') }}</span>
                    <span class="badge {{ $pedido->listo ? 'badge-ready' : 'badge-pending' }}">
                        {{ $pedido->listo ? 'Listo' : 'En preparación' }}
                    </span>
                </div>
                @foreach ($pedido->detalle as $item)
                    <div style="display:flex; justify-content:space-between; font-size:13.5px; padding:2px 0;">
                        <span>{{ $item->nombre_producto }}{{ $item->cantidad > 1 ? " ×{$item->cantidad}" : '' }}</span>
                        <span>Bs {{ number_format($item->subtotal(), 2) }}</span>
                    </div>
                @endforeach
                <div style="display:flex; justify-content:space-between; font-size:13px; border-top:1px solid var(--ink-100); padding-top:6px; margin-top:6px;">
                    <span style="color:var(--ink-500);">Código de recojo: {{ $pedido->codigo_recojo }}</span>
                    <strong style="color:var(--guindo-800);">Bs {{ number_format($pedido->total, 2) }}</strong>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
