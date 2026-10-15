@extends('layouts.app')

@section('titulo', 'Mi perfil')

@section('contenido')
    <h1>Mi perfil</h1>
    <p class="subtitulo">Tus datos y tu seguridad.</p>

    <div class="columnas">
        <section class="tarjeta">
            <h2>Mis datos</h2>

            <div style="margin-bottom:14px">
                @include('partials.avatar', ['user' => $usuario, 'tam' => 96])
            </div>

            <p><strong>Correo:</strong> {{ $usuario->email }}</p>
            <p><strong>Tipo de usuario:</strong> <span class="etiqueta rol-{{ $usuario->rol }}">{{ $usuario->rol_texto }}</span></p>
            <p><strong>Miembro desde:</strong> {{ $usuario->created_at->format('d/m/Y') }}</p>
            @if ($usuario->esCliente())
                <p><strong>Pedidos realizados:</strong> {{ $totalPedidos }}</p>
                <a class="btn contorno" href="{{ route('pedidos.historial') }}">Ver mis pedidos</a>
            @endif
        </section>

        <section class="tarjeta">
            <h2>Editar datos</h2>
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="campo {{ $errors->has('name') ? 'con-error' : '' }}">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}" maxlength="60" required data-solo-letras>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="campo {{ $errors->has('telefono') ? 'con-error' : '' }}">
                    <label for="telefono">Celular</label>
                    <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" inputmode="numeric" maxlength="8" placeholder="71234567" data-solo-numeros>
                    @error('telefono') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="campo {{ $errors->has('foto') ? 'con-error' : '' }}">
                    <label for="foto">Foto de perfil <span class="opcional">(JPG, PNG o WEBP, máx. 2 MB)</span></label>
                    <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp">
                    @error('foto') <span class="error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn">Guardar cambios</button>
            </form>
        </section>
    </div>

    <section class="tarjeta" style="max-width:520px">
        <h2>Cambiar contraseña</h2>
        <form method="POST" action="{{ route('perfil.password') }}" novalidate>
            @csrf

            <div class="campo {{ $errors->has('password_actual') ? 'con-error' : '' }}">
                <label for="password_actual">Contraseña actual</label>
                <input type="password" id="password_actual" name="password_actual" autocomplete="current-password" required>
                @error('password_actual') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="fila-2">
                <div class="campo {{ $errors->has('password') ? 'con-error' : '' }}">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="campo">
                    <label for="password_confirmation">Repite la nueva</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                </div>
            </div>
            <p class="requisitos ayuda">Mínimo 8 caracteres, con al menos una letra y un número.</p>

            <button type="submit" class="btn">Cambiar contraseña</button>
        </form>
    </section>
@endsection

@push('scripts')
    @include('partials.validacion-js')
@endpush
