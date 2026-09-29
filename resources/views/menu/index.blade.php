@extends('layouts.app')
@section('titulo', 'Menú del día · CafeQR')
@section('contenido')

<p style="color:var(--guindo-500); font-weight:600; font-size:12.5px; text-transform:uppercase;">Cafetería universitaria</p>
<h2>Menú del día</h2>
<p style="color:var(--ink-500);">Elige tus productos y págalos con QR antes de pasar a recogerlos.</p>

<form action="{{ route('menu.index') }}" method="GET" style="max-width:380px; margin-bottom:10px;">
    <input type="text" name="q" value="{{ $busqueda }}" placeholder="🔍 Buscar un producto… (ej. café, sandwich, combo)">
</form>

<div class="layout-2col">
    <div class="col-products">
        @forelse ($categorias as $categoria)
            <h3 class="section-title">{{ $categoria->icono }} {{ $categoria->nombre }}</h3>
            <div class="grid-products">
                @foreach ($categoria->productos as $producto)
                    <div class="product">
                        <form action="{{ route('carrito.agregar', $producto) }}" method="POST">
                            @csrf
                            <button type="submit">
                                <div class="icon">{{ $producto->icono }}</div>
                                <h3 style="font-size:14.5px;">{{ $producto->nombre }}</h3>
                                <p class="price">Bs {{ number_format($producto->precio, 0) }}</p>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @empty
            <p style="color:var(--ink-500);">No encontramos productos con ese nombre.</p>
        @endforelse
    </div>

    <div class="col-cart card">
        <h3>🛒 Tu pedido</h3>

        @if (empty($carrito))
            <p style="color:var(--ink-500); font-size:13.5px;">Aún no agregaste productos.<br>Toca un producto del menú para empezar.</p>
        @else
            @php $total = 0; @endphp
            @foreach ($carrito as $productoId => $item)
                @php $total += $item['precio'] * $item['cantidad']; @endphp
                <div class="cart-line">
                    <div>
                        <strong>{{ $item['nombre'] }}</strong><br>
                        <span style="color:var(--ink-500);">Bs {{ number_format($item['precio'], 0) }} c/u</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <form action="{{ route('carrito.actualizar', $productoId) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="delta" value="-1">
                            <button class="qty-btn" type="submit">−</button>
                        </form>
                        <span>{{ $item['cantidad'] }}</span>
                        <form action="{{ route('carrito.actualizar', $productoId) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="delta" value="1">
                            <button class="qty-btn" type="submit">+</button>
                        </form>
                        <form action="{{ route('carrito.quitar', $productoId) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="qty-btn" type="submit">✕</button>
                        </form>
                    </div>
                </div>
            @endforeach
            <div class="cart-total"><span>Total</span><span>Bs {{ number_format($total, 2) }}</span></div>
            <a href="{{ route('pagos.show') }}" class="btn">Continuar al pago</a>
        @endif
    </div>
</div>
@endsection
