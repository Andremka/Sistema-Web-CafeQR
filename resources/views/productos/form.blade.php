@extends('layouts.app')

@php($editando = $producto->exists)

@section('titulo', $editando ? 'Editar producto' : 'Nuevo producto')
@section('ancho', 'angosto')

@section('contenido')
    <h1>{{ $editando ? 'Editar producto' : 'Nuevo producto' }}</h1>
    <p class="subtitulo">Los clientes verán estos datos en el menú.</p>

    <section class="tarjeta">
        <form method="POST" enctype="multipart/form-data" novalidate
              action="{{ $editando ? route('productos.update', $producto) : route('productos.store') }}">
            @csrf
            @if ($editando) @method('PUT') @endif

            <div class="campo {{ $errors->has('nombre') ? 'con-error' : '' }}">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}" maxlength="60" required autofocus>
                @error('nombre') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="campo {{ $errors->has('descripcion') ? 'con-error' : '' }}">
                <label for="descripcion">Descripción <span class="opcional">(opcional)</span></label>
                <textarea id="descripcion" name="descripcion" maxlength="200">{{ old('descripcion', $producto->descripcion) }}</textarea>
                @error('descripcion') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="fila-2">
                <div class="campo {{ $errors->has('precio') ? 'con-error' : '' }}">
                    <label for="precio">Precio (Bs)</label>
                    <input type="text" id="precio" name="precio" value="{{ old('precio', $producto->precio !== null ? number_format((float) $producto->precio, 2, '.', '') : '') }}" inputmode="decimal" placeholder="12.50" required>
                    @error('precio') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="campo {{ $errors->has('categoria') ? 'con-error' : '' }}">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Elige una…</option>
                        @foreach (\App\Models\Producto::CATEGORIAS as $cat => $icono)
                            <option value="{{ $cat }}" {{ old('categoria', $producto->categoria) === $cat ? 'selected' : '' }}>{{ $icono }} {{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('categoria') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="campo {{ $errors->has('imagen') ? 'con-error' : '' }}">
                <label for="imagen">Imagen <span class="opcional">(opcional · JPG, PNG o WEBP · máx. 2 MB)</span></label>
                @if ($editando && $producto->imagen_url)
                    <p><img class="prod-thumb" style="width:80px;height:80px" src="{{ $producto->imagen_url }}" alt="Imagen actual"></p>
                    <label class="check"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la imagen actual</label>
                @endif
                <input type="file" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp">
                @error('imagen') <span class="error">{{ $message }}</span> @enderror
            </div>

            <label class="check"><input type="checkbox" name="disponible" value="1" {{ ($errors->any() ? old('disponible') : ($producto->disponible ?? true)) ? 'checked' : '' }}> Disponible para pedir</label>

            <div class="acciones">
                <button type="submit" class="btn">{{ $editando ? 'Guardar cambios' : 'Agregar al menú' }}</button>
                <a class="btn gris" href="{{ route('productos.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
