<div class="factura-cab">
    <div>
        <h2>FACTURA</h2>
        <div>N.º <strong>{{ $factura->numero_formateado }}</strong></div>
        <div class="ayuda">Original — cliente</div>
    </div>
    <div class="factura-emisor">
        <strong>{{ $emisor['nombre'] }}</strong><br>
        NIT: {{ $emisor['nit'] }}<br>
        {{ $emisor['direccion'] }}
    </div>
</div>

<div class="factura-datos">
    <div><strong>Fecha de emisión:</strong> {{ $factura->emitida_en->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
    <div><strong>Pedido:</strong> #{{ $pedido->id }}</div>
    <div><strong>Forma de pago:</strong> {{ $pedido->metodo_pago === 'efectivo' ? 'Efectivo' : 'QR' }}</div>
    <div><strong>Código de recojo:</strong> {{ $pedido->codigo }}</div>
</div>

<div class="factura-cliente">
    <h3>Datos del cliente</h3>
    <div><strong>Nombre / Razón social:</strong> {{ $clienteNombre }}</div>
    <div><strong>NIT / CI:</strong> {{ $clienteDoc }}</div>
    <div><strong>Correo:</strong> {{ $pedido->user->email }}</div>
</div>

<div class="tabla-wrap">
    <table>
        <thead>
            <tr><th>Cant.</th><th>Descripción</th><th class="num">P. unitario</th><th class="num">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ $detalle->nombre }}</td>
                    <td class="num">Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="num">Bs {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="total">TOTAL: Bs {{ number_format($pedido->total, 2) }}</p>
<p class="en-letras">Son: {{ $enLetras }}</p>

<div class="factura-pie">
    <p class="nota">Documento generado por CafeQR con fines de demostración académica.<br>Conserva este comprobante para cualquier reclamo.</p>
    <img src="{{ $qrFactura }}" alt="QR de la factura {{ $factura->numero_formateado }}">
</div>
