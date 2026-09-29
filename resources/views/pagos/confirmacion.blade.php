@extends('layouts.app')
@section('titulo', 'Pago confirmado · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <div style="width:64px; height:64px; border-radius:50%; background:var(--success-700); color:#fff;
        display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 14px;">✔</div>
    <h2>¡Pago verificado correctamente!</h2>
    <p>Pedido <strong>#{{ $pedido->numero_pedido }}</strong> · tu pedido está siendo preparado por la cafetería.</p>
    <p>Código de recojo:</p>
    <div class="code-box">{{ $pedido->codigo_recojo }}</div>
    <p>
        <button type="button" class="btn-link" id="btn-copiar"
            onclick="copiarCodigo(this, '{{ $pedido->codigo_recojo }}')">📋 Copiar código</button>
    </p>
    <a href="{{ route('pedidos.factura', $pedido) }}" class="btn">Ver factura y enviar código</a>
    <a href="{{ route('pedidos.seguimiento', $pedido) }}" class="btn btn-secondary">Ver seguimiento del pedido</a>
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
