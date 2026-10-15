@extends('layouts.app')

@section('titulo', 'Productos')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Productos</h1>
            <p class="subtitulo" style="margin:0">Agrega, edita, quita o marca como agotado lo que se vende en la cafetería.</p>
        </div>
        <a class="btn" href="{{ route('productos.create') }}">+ Nuevo producto</a>
    </div>

    <details class="tarjeta">
        <summary style="cursor:pointer;font-weight:650">📷 Poner fotos a muchos productos de una vez</summary>
        <p class="ayuda" style="margin-top:10px">Copia las fotos (JPG, PNG o WEBP) a la carpeta <code>public\productos</code> del proyecto, con el nombre que ves abajo. No hace falta editar nada: aparecen solas en el menú. Si prefieres una por una, usa <strong>Editar</strong>.</p>
        <p class="ayuda">Ejemplos: «Café americano» → <code>cafe-americano.jpg</code>; «Salteña» → <code>saltena.png</code>. Se recomienda foto horizontal, de menos de 2 MB.</p>
    </details>

    <section class="tarjeta">
        <form class="filtros" method="GET" action="{{ route('productos.index') }}">
            <div class="campo">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $buscar }}" placeholder="Nombre del producto" maxlength="60">
            </div>
            <div class="campo">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria">
                    <option value="todas">Todas</option>
                    @foreach (\App\Models\Producto::CATEGORIAS as $cat => $icono)
                        <option value="{{ $cat }}" {{ $categoria === $cat ? 'selected' : '' }}>{{ $icono }} {{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="disponibles" {{ $estado === 'disponibles' ? 'selected' : '' }}>Disponibles</option>
                    <option value="agotados" {{ $estado === 'agotados' ? 'selected' : '' }}>Agotados</option>
                </select>
            </div>
            <button type="submit" class="btn">Filtrar</button>
        </form>

        @if ($productos->count())
            <div class="tabla-wrap">
                <table>
                    <thead>
                        <tr><th></th><th>Producto</th><th>Categoría</th><th class="num">Precio</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr>
                                <td>
                                    @if ($producto->imagen_url)
                                        <img class="prod-thumb" src="{{ $producto->imagen_url }}" alt="">
                                    @else
                                        <span class="prod-thumb">{{ $producto->icono }}</span>
                                    @endif
                                </td>
                                <td><strong>{{ $producto->nombre }}</strong><br><span class="ayuda" title="Nombre de archivo para la foto">📷 {{ $producto->nombre_archivo_foto }}.jpg</span><br><span class="ayuda">{{ \Illuminate\Support\Str::limit($producto->descripcion, 60) }}</span></td>
                                <td>{{ $producto->categoria }}</td>
                                <td class="num">Bs {{ number_format($producto->precio, 2) }}</td>
                                <td><span class="etiqueta {{ $producto->disponible ? 'disponible' : 'agotado' }}">{{ $producto->disponible ? 'Disponible' : 'Agotado' }}</span></td>
                                <td>
                                    <div class="acciones-fila">
                                        <form method="POST" action="{{ route('productos.alternar', $producto) }}">
                                            @csrf
                                            <button type="submit" class="btn chico gris">{{ $producto->disponible ? 'Marcar agotado' : 'Marcar disponible' }}</button>
                                        </form>
                                        <a class="btn chico contorno" href="{{ route('productos.edit', $producto) }}">Editar</a>
                                        <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Quitar «{{ addslashes($producto->nombre) }}» del menú?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn chico rojo">Quitar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $productos->links() }}
        @else
            <div class="vacio"><span class="grande">🍽️</span>No hay productos con esos filtros.</div>
        @endif
    </section>
@endsection
