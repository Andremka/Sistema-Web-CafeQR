@extends('emails.layout')

@section('contenido')
    @php($enlace = \App\Support\UrlPublica::base(request()).route('pedido.seguimiento', $pedido, false))
    <p style="margin:0 0 12px;">Hola, <strong>{{ $usuario->name }}</strong>:</p>
    <p style="margin:0 0 12px;"><strong>¡Pago confirmado!</strong> Tu pedido <strong>#{{ $pedido->id }}</strong> ya está en la cola de la cafetería.</p>

    <p style="text-align:center;margin:18px 0 6px;color:#6e5a5f;font-size:13px;">Tu código de recojo</p>
    <p style="text-align:center;margin:0 0 18px;">
        <span style="display:inline-block;font-family:Consolas,monospace;font-size:34px;font-weight:800;letter-spacing:8px;color:#7d1126;background:#f8ebee;border:2px dashed #7d1126;border-radius:14px;padding:10px 22px 10px 30px;">{{ $pedido->codigo }}</span>
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        @foreach ($pedido->detalles as $detalle)
            <tr>
                <td style="padding:6px 0;border-bottom:1px solid #eee3e5;">{{ $detalle->cantidad }} × {{ $detalle->nombre }}</td>
                <td align="right" style="padding:6px 0;border-bottom:1px solid #eee3e5;">Bs {{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
        @endforeach
        @if ($pedido->descuento > 0)
            <tr>
                <td style="padding:6px 0;color:#6e5a5f;">Cupón {{ $pedido->cupon }}</td>
                <td align="right" style="padding:6px 0;color:#6e5a5f;">−Bs {{ number_format($pedido->descuento, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:10px 0;"><strong>Total</strong></td>
            <td align="right" style="padding:10px 0;"><strong>Bs {{ number_format($pedido->total, 2) }}</strong></td>
        </tr>
    </table>

    @if ($pedido->factura)
        <p style="margin:6px 0 14px;color:#6e5a5f;font-size:13px;">Factura N.º {{ $pedido->factura->numero_formateado }} · {{ $pedido->metodo_pago === 'efectivo' ? 'Pago en efectivo' : 'Pago con QR' }}</p>
    @endif

    <p style="margin:0 0 16px;">Te avisaremos por correo cuando tu pedido esté <strong>en preparación</strong> y cuando esté <strong>listo</strong>.</p>
    <p style="text-align:center;margin:18px 0;">
        <a href="{{ $enlace }}" style="background:#7d1126;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600;display:inline-block;">Seguir mi pedido</a>
    </p>
@endsection
