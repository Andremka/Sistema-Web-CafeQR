@extends('layouts.guest')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <h1>Crear cuenta</h1>
    <p class="subtitulo">Regístrate con tu correo institucional para hacer pedidos.</p>

    <form method="POST" action="{{ route('registro.guardar') }}" novalidate>
        @csrf

        <div class="campo {{ $errors->has('name') ? 'con-error' : '' }}">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="60" autocomplete="name"
                   placeholder="Ej.: María Fernández" required autofocus data-solo-letras>
            <p class="requisitos">Solo letras y espacios. No se aceptan números.</p>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="campo {{ $errors->has('email') ? 'con-error' : '' }}">
            <label for="email">Correo institucional</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="120" autocomplete="email"
                   placeholder="tunombre@est.{{ explode(',', config('cafeqr.dominio'))[0] }}" required>
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="campo {{ $errors->has('telefono') ? 'con-error' : '' }}">
            <label for="telefono">Celular <span class="opcional">(opcional)</span></label>
            <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" inputmode="numeric" maxlength="8"
                   placeholder="71234567" autocomplete="tel-national" data-solo-numeros>
            @error('telefono') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="fila-2">
            <div class="campo {{ $errors->has('password') ? 'con-error' : '' }}">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="campo">
                <label for="password_confirmation">Repite la contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
            </div>
        </div>
        <p class="requisitos">Mínimo 8 caracteres, con al menos una letra y un número.</p>

        <button type="submit" class="enviar">Crear mi cuenta</button>
    </form>

    <p class="pie">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
@endsection

@push('scripts')
    @include('partials.validacion-js')
@endpush
