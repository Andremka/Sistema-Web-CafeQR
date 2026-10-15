@extends('layouts.app')

@section('titulo', 'Todos los pedidos')

@section('contenido')
    <h1>Todos los pedidos</h1>
    <p class="subtitulo">Historial completo de la cafetería.</p>

    <section class="tarjeta">
        <form class="filtros" method="GET" action="{{ route('admin.pedidos') }}">
            <div class="campo">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $buscar }}" placeholder="Código, N.º de pedido, nombre o correo" maxlength="60">
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="todos">Todos</option>
                    @foreach (\App\Models\Pedido::TEXTOS as $clave => $texto)
                        @if ($clave !== 'carrito')
                            <option value="{{ $clave }}" {{ $estado === $clave ? 'selected' : '' }}>{{ $texto }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn">Filtrar</button>
        </form>

        @if ($pedidos->count())
            <div class="tabla-wrap">
                <table>
                    <thead><tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Pago</th><th class="num">Total</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td>#{{ $pedido->id }}@if ($pedido->codigo)<br><span class="codigo">{{ $pedido->codigo }}</span>@endif</td>
                                <td>{{ $pedido->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td>{{ $pedido->user->name }}<br><span class="ayuda">{{ $pedido->user->email }}</span></td>
                                <td>@foreach ($pedido->detalles as $d){{ $d->cantidad }}× {{ $d->nombre }}@if (! $loop->last), @endif @endforeach</td>
                                <td>{{ $pedido->metodo_pago ? ($pedido->metodo_pago === 'efectivo' ? 'Efectivo' : 'QR') : '—' }}</td>
                                <td class="num">Bs {{ number_format($pedido->total, 2) }}</td>
                                <td><span class="etiqueta estado-{{ $pedido->estado }}">{{ $pedido->estado_texto }}</span>@if ($pedido->reembolso_pendiente)<br><span class="ayuda">Reembolso pendiente</span>@endif</td>
                                <td>
                                    @if ($pedido->pagado_en && $pedido->estado !== 'cancelado')
                                        <a class="btn chico contorno" href="{{ route('factura.show', $pedido) }}">Factura</a>
                                    @endif
                                    @if ($pedido->puedeCancelarPersonal())
                                        <details class="cancelar-pedido">
                                            <summary>Cancelar</summary>
                                            <form class="form-mini" method="POST" action="{{ route('entregas.cancelar', $pedido) }}">
                                                @csrf
                                                <input type="text" name="motivo" minlength="5" maxlength="200" placeholder="Motivo (mín. 5 letras)" required>
                                                <button type="submit" class="btn chico rojo">Cancelar pedido</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $pedidos->links() }}
        @else
            <div class="vacio"><span class="grande">🧾</span>No hay pedidos con esos filtros.</div>
        @endif
    </section>
@endsection
