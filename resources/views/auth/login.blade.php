@extends('layouts.guest')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <h1>Iniciar sesión</h1>
    <p class="subtitulo">Ingresa con tu correo para pedir o gestionar la cafetería.</p>

    <form method="POST" action="{{ route('login.entrar') }}" novalidate>
        @csrf

        <div class="campo {{ $errors->has('email') ? 'con-error' : '' }}">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tunombre@est.univalle.edu" autocomplete="email" required autofocus>
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="campo {{ $errors->has('password') ? 'con-error' : '' }}">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
            @error('password') <span class="error">{{ $message }}</span> @enderror
        </div>

        <label class="check"><input type="checkbox" name="recordar" value="1"> Mantener la sesión iniciada</label>

        <button type="submit" class="enviar">Entrar</button>
    </form>

    <p class="pie"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
    <p class="pie">¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a></p>
@endsection
