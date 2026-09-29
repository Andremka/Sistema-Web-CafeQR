@extends('layouts.app')
@section('titulo', 'Iniciar sesión · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <h2>☕ CafeQR</h2>
    <p style="color:var(--ink-500);">Accede con tu correo institucional</p>

    @if (session('exito'))
        <div class="alert alert-success">✓ {{ session('exito') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login.store') }}" method="POST">
        @csrf
        <div class="field">
            <label for="email">Correo institucional</label>
            <input type="text" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@univalle.edu">
        </div>
        <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="••••••••">
        </div>
        <button type="submit" class="btn">Ingresar</button>
    </form>

    <p style="margin-top:16px;"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
    <p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
</div>
@endsection
