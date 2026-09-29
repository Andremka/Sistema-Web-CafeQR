@extends('layouts.app')
@section('titulo', 'Factura de pago · CafeQR')
@section('contenido')
<p><a href="{{ route('menu.index') }}">← Volver al menú</a></p>

<div class="card card-mid" id="factura">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1.5px dashed var(--ink-100); padding-bottom:14px; margin-bottom:14px;">
        <div>
            <h2>Factura de pago</h2>
            <p style="color:var(--ink-500);">CafeQR · Universidad Privada del Valle</p>
        </div>
        <span style="background:var(--success-100); color:var(--success-700); font-weight:700; font-size:13px; padding:6px 12px; border-radius:999px;">✅ Pagado</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:14px; margin-bottom:16px;">
        <div><span style="display:block; font-size:11.5px; color:var(--ink-500); text-transform:uppercase;">N.º de pedido</span><strong>#{{ $pedido->numero_pedido }}</strong></div>
        <div><span style="display:block; font-size:11.5px; color:var(--ink-500); text-transform:uppercase;">Fecha</span><strong>{{ $pedido->created_at->format('d/m/Y') }}</strong></div>
        <div><span style="display:block; font-size:11.5px; color:var(--ink-500); text-transform:uppercase;">Hora</span><strong>{{ $pedido->created_at->format('h:i A') }}</strong></div>
        <div>
            <span style="display:block; font-size:11.5px; color:var(--ink-500); text-transform:uppercase;">Código de recojo</span>
            <strong>{{ $pedido->codigo_recojo }}</strong>
            <button type="button" class="btn-link no-print" style="display:block; font-size:11.5px;"
                onclick="copiarCodigo(this, '{{ $pedido->codigo_recojo }}')">📋 Copiar</button>
        </div>
    </div>

    <table class="invoice">
        <thead><tr><th>Producto</th><th>Cant.</th><th>P. unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
        @foreach ($pedido->detalle as $item)
            <tr>
                <td>{{ $item->nombre_producto }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>Bs {{ number_format($item->precio_unitario, 2) }}</td>
                <td>Bs {{ number_format($item->subtotal(), 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="3" style="font-weight:700;">Total</td><td style="font-weight:700; color:var(--guindo-800);">Bs {{ number_format($pedido->total, 2) }}</td></tr></tfoot>
    </table>

    <p style="display:flex; justify-content:space-between; font-size:13.5px; border-top:1px solid var(--ink-100); padding-top:8px;">
        <span style="color:var(--ink-500);">Pagado a</span>
        <strong>{{ $pedido->pago->banco_destino }} · {{ $pedido->pago->titular_destino }} · Nro {{ $pedido->pago->numero_destino }}</strong>
    </p>
    <p style="display:flex; justify-content:space-between; font-size:13.5px;">
        <span style="color:var(--ink-500);">Cliente</span>
        <strong>{{ $pedido->usuario->name }} ({{ $pedido->usuario->email }})</strong>
    </p>
</div>

<div class="card card-mid no-print">
    <h3>Recibir el código de recojo</h3>
    <p style="color:var(--ink-500);">Además de verlo aquí, elige cómo quieres que te lo enviemos.</p>
    <form action="{{ route('pedidos.enviar-codigo', $pedido) }}" method="POST">
        @csrf
        <label><input type="checkbox" name="enviar_correo" value="1" checked> Correo electrónico</label>
        <div class="field"><input type="email" name="correo" value="{{ $pedido->usuario->email }}"></div>

        <label><input type="checkbox" name="enviar_sms" value="1"> Mensaje de texto (SMS)</label>
        <div class="field"><input type="tel" name="telefono" placeholder="Número de celular"></div>

        <button type="submit" class="btn">Enviar código</button>
    </form>
</div>

<div class="no-print" style="max-width:640px; margin:10px auto 0; display:flex; gap:10px;">
    <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir factura</button>
    <a href="{{ route('pedidos.seguimiento', $pedido) }}" class="btn">Ver seguimiento del pedido</a>
</div>

<script>
function copiarCodigo(boton, codigo) {
    navigator.clipboard.writeText(String(codigo)).then(function () {
        var textoOriginal = boton.textContent;
        boton.textContent = '✓ Copiado';
        setTimeout(function () { boton.textContent = textoOriginal; }, 1500);
    });
}
</script>
@endsection
