@extends('emails.layout')

@section('contenido')
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 16px;">Recibimos tu pago. Para confirmar tu compra escribe este código en la pantalla de CafeQR:</p>
    <p style="text-align:center;margin:18px 0;">
        <span style="display:inline-block;font-family:Consolas,monospace;font-size:36px;font-weight:800;letter-spacing:10px;color:#7d1126;background:#f8ebee;border:2px dashed #7d1126;border-radius:14px;padding:12px 24px 12px 32px;">{{ $codigo }}</span>
    </p>
    <p style="margin:0 0 8px;">El código vale por <strong>{{ $minutos }} minutos</strong> y solo se puede usar una vez.</p>
    <p style="margin:0 0 16px;color:#6e5a5f;">Si tú no hiciste este pedido, no compartas el código con nadie.</p>
@endsection
