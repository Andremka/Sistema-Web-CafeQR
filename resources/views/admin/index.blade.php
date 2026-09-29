@extends('layouts.app')
@section('titulo', 'Panel de administración · CafeQR')
@section('contenido')

<p style="color:var(--guindo-500); font-weight:600; font-size:12.5px; text-transform:uppercase;">Panel de administración</p>
<h2>Todos los pedidos</h2>
<p style="color:var(--ink-500);">Historial completo de compras de todos los estudiantes en CafeQR.</p>

<div class="admin-stats">
    <div class="admin-stat"><span>Pedidos totales</span><strong>{{ $pedidos->count() }}</strong></div>
    <div class="admin-stat"><span>Ventas totales</span><strong>Bs {{ number_format($totalVentas, 2) }}</strong></div>
    <div class="admin-stat"><span>Productos vendidos</span><strong>{{ $totalProductos }}</strong></div>
    <div class="admin-stat"><span>Más vendido</span><strong>{{ $masVendido }}</strong></div>
</div>

<div class="card">
    @forelse ($pedidos as $pedido)
        <div class="order-entry">
            <div class="order-top">
                <strong style="color:var(--guindo-700);">Pedido #{{ $pedido->numero_pedido }}</strong>
                <span>{{ $pedido->created_at->format('d/m/Y · h:i A') }}</span>
                <span class="badge {{ $pedido->listo ? 'badge-ready' : 'badge-pending' }}">
                    {{ $pedido->listo ? 'Listo' : 'En preparación' }}
                </span>
            </div>
            <div style="font-weight:600;">{{ $pedido->usuario->name }} ({{ $pedido->usuario->email }})</div>
            <div style="font-size:13.5px; color:var(--ink-700); margin:4px 0;">
                @foreach ($pedido->detalle as $item)
                    {{ $item->nombre_producto }}{{ $item->cantidad > 1 ? " ×{$item->cantidad}" : '' }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px; border-top:1px solid var(--ink-100); padding-top:6px;">
                <span style="color:var(--ink-500);">Código de recojo: {{ $pedido->codigo_recojo }}</span>
                <strong style="color:var(--guindo-800);">Bs {{ number_format($pedido->total, 2) }}</strong>
            </div>
        </div>
    @empty
        <p style="color:var(--ink-500);">Aún no hay pedidos registrados.</p>
    @endforelse
</div>
@endsection
