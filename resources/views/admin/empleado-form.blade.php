@extends('layouts.app')

@section('titulo', 'Nuevo empleado')
@section('ancho', 'angosto')

@section('contenido')
    <h1>Nuevo empleado</h1>
    <p class="subtitulo">El empleado podrá gestionar productos, confirmar pagos y atender entregas. No tiene acceso a usuarios ni facturas.</p>

    <section class="tarjeta">
        <form method="POST" action="{{ route('admin.empleado.guardar') }}" novalidate>
            @csrf

            <div class="campo {{ $errors->has('name') ? 'con-error' : '' }}">
                <label for="name">Nombre completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="60" required autofocus data-solo-letras>
                <p class="requisitos ayuda">Solo letras y espacios. No se aceptan números.</p>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="campo {{ $errors->has('email') ? 'con-error' : '' }}">
                <label for="email">Correo</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="120" required>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="campo {{ $errors->has('telefono') ? 'con-error' : '' }}">
                <label for="telefono">Celular <span class="opcional">(opcional)</span></label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" inputmode="numeric" maxlength="8" placeholder="71234567" data-solo-numeros>
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
            <p class="ayuda">Mínimo 8 caracteres, con al menos una letra y un número. Compártela con el empleado; podrá cambiarla desde su perfil.</p>

            <div class="acciones">
                <button type="submit" class="btn">Crear empleado</button>
                <a class="btn gris" href="{{ route('admin.usuarios') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    @include('partials.validacion-js')
@endpush
