@extends('layouts.guest')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <h1>Recuperar contraseña</h1>
    <p class="subtitulo">Escribe tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="campo {{ $errors->has('email') ? 'con-error' : '' }}">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tunombre@est.univalle.edu" required autofocus>
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="enviar">Enviar enlace</button>
    </form>

    <p class="pie"><a href="{{ route('login') }}">← Volver a iniciar sesión</a></p>
@endsection
