@extends('layouts.app')
@section('titulo', 'Nueva contraseña · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <h2>Nueva contraseña</h2>
    <p style="color:var(--ink-500);">Elige una contraseña nueva para tu cuenta</p>

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field">
            <label for="email">Correo institucional</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
                   autocomplete="email" required>
        </div>
        <div class="field">
            <label for="password">Nueva contraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" autocomplete="new-password" minlength="8" required autofocus>
        </div>
        <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="••••••••" autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn">Guardar contraseña</button>
    </form>
</div>
@endsection