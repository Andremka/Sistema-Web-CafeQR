@extends('emails.layout')

@section('contenido')
    @php
        $enlace = \App\Support\UrlPublica::base(request()).route('pedido.seguimiento', $pedido, false);
        $hora = $pedido->listo_estimado?->timezone(config('app.timezone'))->format('H:i');
    @endphp
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 12px;">👨‍🍳 Tu pedido <strong>#{{ $pedido->id }}</strong> ya está <strong>en preparación</strong>.</p>

    <p style="text-align:center;margin:18px 0;">
        <span style="display:block;color:#6e5a5f;font-size:13px;">Tiempo estimado</span>
        <span style="display:inline-block;font-size:40px;font-weight:800;color:#9a3412;">{{ $minutos }} min</span>
        @if ($hora)
            <span style="display:block;color:#6e5a5f;font-size:13px;">Estará listo aproximadamente a las {{ $hora }}</span>
        @endif
    </p>

    <p style="margin:0 0 6px;">Tu código de recojo es:</p>
    <p style="text-align:center;margin:0 0 16px;">
        <span style="display:inline-block;font-family:Consolas,monospace;font-size:30px;font-weight:800;letter-spacing:8px;color:#7d1126;background:#f8ebee;border:2px dashed #7d1126;border-radius:14px;padding:8px 20px 8px 28px;">{{ $pedido->codigo }}</span>
    </p>
    <p style="margin:0 0 16px;">Te enviaremos otro correo apenas esté <strong>listo para recoger</strong>.</p>
    <p style="text-align:center;margin:18px 0;">
        <a href="{{ $enlace }}" style="background:#7d1126;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600;display:inline-block;">Ver el avance</a>
    </p>
@endsection
