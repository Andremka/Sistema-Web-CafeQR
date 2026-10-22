@extends('emails.layout')

@section('contenido')
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 16px;">¡Gracias por registrarte en <strong>CafeQR</strong>! Ya puedes pedir en la cafetería, pagar con QR o en efectivo y recoger sin hacer fila.</p>
    <p style="margin:0 0 16px;">Confirma que este correo es tuyo para recibir sin problemas tus códigos de pago y los avisos de tus pedidos:</p>
    <p style="text-align:center;margin:20px 0;">
        <a href="{{ $url }}" style="background:#7d1126;color:#ffffff;text-decoration:none;padding:13px 24px;border-radius:10px;font-weight:600;display:inline-block;">Confirmar mi correo</a>
    </p>
    <p style="margin:0 0 12px;color:#6e5a5f;font-size:13px;">Si el botón no funciona, copia y pega esta dirección en tu navegador:<br><span style="word-break:break-all;">{{ $url }}</span></p>
    <p style="margin:0 0 16px;color:#6e5a5f;">Si tú no creaste esta cuenta, ignora este mensaje.</p>
@endsection
