@extends('layouts.app')

@section('titulo', 'Facturas')

@section('contenido')
    <h1>Facturas emitidas</h1>
    <p class="subtitulo">Todas las facturas generadas por los pagos confirmados.</p>

    <section class="tarjeta">
        <form class="filtros" method="GET" action="{{ route('admin.facturas') }}">
            <div class="campo">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $buscar }}" placeholder="N.º de factura, cliente o NIT/CI" maxlength="60">
            </div>
            <button type="submit" class="btn">Buscar</button>
            @if ($buscar !== '') <a class="btn gris" href="{{ route('admin.facturas') }}">Limpiar</a> @endif
        </form>

        @if ($facturas->count())
            <div class="tabla-wrap">
                <table>
                    <thead><tr><th>Factura</th><th>Fecha</th><th>Cliente</th><th>NIT/CI</th><th>Pedido</th><th class="num">Total</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($facturas as $factura)
                            <tr>
                                <td><strong>{{ $factura->numero_formateado }}</strong></td>
                                <td>{{ $factura->emitida_en->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td>{{ $factura->pedido->razon_social ?: $factura->pedido->user->name }}<br><span class="ayuda">{{ $factura->pedido->user->email }}</span></td>
                                <td>{{ $factura->pedido->nit_ci ?: 'S/N' }}</td>
                                <td>#{{ $factura->pedido->id }} <span class="etiqueta estado-{{ $factura->pedido->estado }}">{{ $factura->pedido->estado_texto }}</span></td>
                                <td class="num">Bs {{ number_format($factura->pedido->total, 2) }}</td>
                                <td><a class="btn chico contorno" href="{{ route('factura.show', $factura->pedido) }}">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $facturas->links() }}
        @else
            <div class="vacio"><span class="grande">🧾</span>No hay facturas para mostrar.</div>
        @endif
    </section>
@endsection
