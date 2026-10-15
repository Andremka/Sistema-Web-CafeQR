@extends('layouts.app')

@section('titulo', 'Menú')

@section('contenido')
    @php
        $u = auth()->user();
        $primerNombre = \Illuminate\Support\Str::before($u->name, ' ');
    @endphp

    <section class="hero">
        @include('partials.logo-univalle')
        <h1>¡Hola, {{ $primerNombre }}!</h1>
        <p>Elige lo que quieras, paga con QR o en efectivo y recógelo sin hacer fila.</p>

        <form class="buscador" method="GET" action="{{ route('dashboard') }}" role="search">
            <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar un producto…" aria-label="Buscar producto" maxlength="60">
            <button type="submit" class="btn">Buscar</button>
        </form>
    </section>

    @if (! $u->esCliente())
        <div class="aviso">Estás viendo el menú como <strong>{{ $u->rol_texto }}</strong>. Para agregar, editar o agotar productos ve a <a href="{{ route('productos.index') }}">Productos</a>.</div>
    @endif

    @if ($buscar !== '')
        <p class="ayuda">Resultados para “{{ $buscar }}” · <a href="{{ route('dashboard') }}">ver todo el menú</a></p>
    @endif

    @if (count($categorias) > 1)
        <nav class="chips" aria-label="Categorías">
            @foreach ($categorias as $categoria => $items)
                <a href="#cat-{{ \Illuminate\Support\Str::slug($categoria) }}">{{ \App\Models\Producto::CATEGORIAS[$categoria] ?? '🍽️' }} {{ $categoria }}</a>
            @endforeach
        </nav>
    @endif

    @forelse ($categorias as $categoria => $items)
        <section class="seccion cat-{{ \Illuminate\Support\Str::slug($categoria) }}" id="cat-{{ \Illuminate\Support\Str::slug($categoria) }}">
            <h2 class="seccion-titulo">
                <span class="ico">{{ \App\Models\Producto::CATEGORIAS[$categoria] ?? '🍽️' }}</span> {{ $categoria }}
                <span class="cuenta">{{ $items->count() }} {{ $items->count() === 1 ? 'producto' : 'productos' }}</span>
            </h2>

            <div class="grilla">
                @foreach ($items as $producto)
                    <article class="producto {{ $producto->categoria_clase }} {{ $producto->disponible ? '' : 'sin-stock' }}">
                        <div class="producto-img">
                            @if ($producto->imagen_url)
                                <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" loading="lazy">
                            @else
                                {{ $producto->icono }}
                            @endif
                        </div>
                        <div class="producto-cuerpo">
                            <h3>{{ $producto->nombre }}</h3>
                            <p class="desc">{{ $producto->descripcion }}</p>
                            <p class="precio">Bs {{ number_format($producto->precio, 2) }}</p>

                            @if (! $producto->disponible)
                                <span class="etiqueta agotado">Agotado</span>
                            @elseif ($u->esCliente())
                                <form class="agregar" method="POST" action="{{ route('pedido.agregar', $producto) }}">
                                    @csrf
                                    <input type="number" name="cantidad" value="1" min="1" max="20" aria-label="Cantidad de {{ $producto->nombre }}">
                                    <button type="submit" class="btn">Agregar</button>
                                </form>
                            @else
                                <span class="etiqueta disponible">Disponible</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="tarjeta vacio">
            <span class="grande">🔎</span>
            @if ($buscar !== '')
                No encontramos productos con “{{ $buscar }}”.
            @else
                Todavía no hay productos en el menú.
            @endif
        </div>
    @endforelse
@endsection
