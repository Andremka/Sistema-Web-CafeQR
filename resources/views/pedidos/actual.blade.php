@extends('layouts.app')

@section('titulo', 'Mi pedido')
@section('ancho', 'angosto')

@section('contenido')
    <h1>Mi pedido</h1>

    @if (! $pedido)
        <div class="tarjeta vacio">
            <span class="grande">🛒</span>
            Tu pedido está vacío.<br>
            <a class="btn" href="{{ route('dashboard') }}">Ver el menú</a>
        </div>
    @else
        <section class="tarjeta">
            <div class="pedido-cab">
                <h2 style="margin:0">Pedido #{{ $pedido->id }}</h2>
                <span class="etiqueta estado-{{ $pedido->estado }}">{{ $pedido->estado_texto }}</span>
            </div>

            @if ($pedido->estado === 'carrito')
                <p class="ayuda">Revisa tu pedido. Puedes cambiar cantidades o quitar productos antes de pagar.</p>
            @else
                <div class="aviso" style="margin-top:12px">Tu pedido está esperando el pago. Puedes continuar, modificarlo o cancelarlo.</div>
            @endif

            <div class="tabla-wrap">
                <table>
                    <thead>
                        <tr><th>Producto</th><th>Cant.</th><th class="num">Precio</th><th class="num">Subtotal</th>@if ($pedido->estado === 'carrito')<th></th>@endif</tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->nombre }}
                                    @if ($detalle->producto && ! $detalle->producto->disponible)
                                        <span class="etiqueta agotado">Agotado</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($pedido->estado === 'carrito')
                                        <form class="cant-form" method="POST" action="{{ route('pedido.cambiar', $detalle) }}">
                                            @csrf @method('PATCH')
                                            <input type="number" name="cantidad" value="{{ $detalle->cantidad }}" min="1" max="20" aria-label="Cantidad de {{ $detalle->nombre }}">
                                            <button type="submit" class="btn chico gris">Actualizar</button>
                                        </form>
                                    @else
                                        {{ $detalle->cantidad }}
                                    @endif
                                </td>
                                <td class="num">Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="num">Bs {{ number_format($detalle->subtotal, 2) }}</td>
                                @if ($pedido->estado === 'carrito')
                                    <td>
                                        <form method="POST" action="{{ route('pedido.quitar', $detalle) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="enlace-peligro">Quitar</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="total">Total a pagar: Bs {{ number_format($pedido->total, 2) }}</p>

            <div class="acciones">
                @if ($pedido->estado === 'carrito')
                    <a class="btn gris" href="{{ route('dashboard') }}">+ Seguir pidiendo</a>
                    <a class="btn" href="{{ route('pedido.confirmar') }}">Continuar →</a>
                @else
                    <a class="btn" href="{{ route('pedido.continuar') }}">Continuar con el pago →</a>
                    <form method="POST" action="{{ route('pedido.modificar') }}">
                        @csrf
                        <button type="submit" class="btn gris">Modificar pedido</button>
                    </form>
                @endif
            </div>
        </section>

        @if ($pedido->estado === 'pendiente_pago')
            <section class="tarjeta">
                <details class="cancelar-pedido">
                    <summary>Cancelar este pedido</summary>
                    <form method="POST" action="{{ route('pedido.cancelar', $pedido) }}">
                        @csrf
                        <div class="campo">
                            <label for="motivo">Motivo <span class="opcional">(opcional)</span></label>
                            <textarea id="motivo" name="motivo" maxlength="200" placeholder="Cuéntanos por qué lo cancelas"></textarea>
                        </div>
                        <button type="submit" class="btn rojo">Sí, cancelar pedido</button>
                    </form>
                </details>
            </section>
        @endif
    @endif
@endsection
