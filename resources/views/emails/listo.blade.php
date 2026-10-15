@extends('emails.layout')

@section('contenido')
    @php($enlace = \App\Support\UrlPublica::base(request()).route('pedido.seguimiento', $pedido, false))
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 12px;font-size:18px;">🎉 <strong>¡Tu pedido #{{ $pedido->id }} está listo para recoger!</strong></p>
    <p style="margin:0 0 6px;">Pasa por la cafetería y muestra este código:</p>
    <p style="text-align:center;margin:0 0 18px;">
        <span style="display:inline-block;font-family:Consolas,monospace;font-size:38px;font-weight:800;letter-spacing:10px;color:#166534;background:#ecfdf5;border:2px dashed #16a34a;border-radius:14px;padding:12px 24px 12px 34px;">{{ $pedido->codigo }}</span>
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        @foreach ($pedido->detalles as $detalle)
            <tr><td style="padding:5px 0;border-bottom:1px solid #eee3e5;">{{ $detalle->cantidad }} × {{ $detalle->nombre }}</td></tr>
        @endforeach
    </table>
    <p style="text-align:center;margin:20px 0;">
        <a href="{{ $enlace }}" style="background:#7d1126;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600;display:inline-block;">Ver mi pedido</a>
    </p>
@endsection
