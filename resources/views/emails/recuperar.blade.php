@extends('emails.layout')

@section('contenido')
    <p style="margin:0 0 12px;">Hola, <strong>{{ $nombre }}</strong>:</p>
    <p style="margin:0 0 16px;">Recibimos una solicitud para crear una nueva contraseña en CafeQR. Pulsa el botón para continuar:</p>
    <p style="text-align:center;margin:20px 0;">
        <a href="{{ $url }}" style="background:#7d1126;color:#ffffff;text-decoration:none;padding:13px 24px;border-radius:10px;font-weight:600;display:inline-block;">Crear nueva contraseña</a>
    </p>
    <p style="margin:0 0 12px;">El enlace vence en <strong>{{ $minutos }} minutos</strong>.</p>
    <p style="margin:0 0 12px;color:#6e5a5f;font-size:13px;">Si el botón no funciona, copia y pega esta dirección en tu navegador:<br><span style="word-break:break-all;">{{ $url }}</span></p>
    <p style="margin:0 0 16px;color:#6e5a5f;">Si tú no lo pediste, ignora este mensaje: tu contraseña no cambiará.</p>
@endsection
