@extends('layouts.app')
@section('titulo', 'Pago no procesado · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <div style="width:64px; height:64px; border-radius:50%; background:var(--danger-700); color:#fff;
        display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 14px;">✕</div>
    <h2>No se pudo procesar el pago</h2>
    <p>Hubo un problema al confirmar la transacción. Verifica tu saldo o intenta con otro método.</p>
    <a href="{{ route('pagos.show') }}" class="btn">Reintentar pago</a>
    <a href="{{ route('menu.index') }}" class="btn btn-secondary">Volver al menú</a>
</div>
@endsection
