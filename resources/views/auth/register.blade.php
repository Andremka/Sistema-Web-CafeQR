@extends('layouts.app')
@section('titulo', 'Crear cuenta · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <h2>Crear cuenta</h2>
    <p style="color:var(--ink-500);">Regístrate con tu correo institucional</p>

    <form action="{{ route('register.store') }}" method="POST">
        @csrf
        <div class="field">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   placeholder="Nombre y apellido" autocomplete="name" maxlength="100" required autofocus>
        </div>
        <div class="field">
            <label for="email">Correo institucional</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="usuario@univalle.edu" autocomplete="email" required>
        </div>
        <div class="field">
            <label for="password">Contraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="••••••••" autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn">Crear cuenta</button>
    </form>

    <p style="margin-top:16px;">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div>
@endsection