@extends('emails.layout')

@section('contenido')
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 12px;">Tu pedido <strong>#{{ $pedido->id }}</strong> fue <strong style="color:#b42318;">cancelado</strong> por {{ $pedido->cancelado_por === 'cliente' ? 'ti' : 'la cafetería' }}.</p>
    @if ($pedido->motivo_cancelacion)
        <p style="margin:0 0 12px;background:#fef2f2;border-radius:10px;padding:10px 14px;"><strong>Motivo:</strong> {{ $pedido->motivo_cancelacion }}</p>
    @endif
    @if ($pedido->reembolso_pendiente)
        <p style="margin:0 0 12px;">💸 Como ya habías pagado <strong>Bs {{ number_format($pedido->total, 2) }}</strong>, acércate a la cafetería para que te devuelvan tu dinero.</p>
    @else
        <p style="margin:0 0 12px;">No se te cobró nada por este pedido.</p>
    @endif
    <p style="margin:0 0 16px;color:#6e5a5f;">Cuando quieras, puedes hacer un nuevo pedido desde CafeQR.</p>
@endsection
