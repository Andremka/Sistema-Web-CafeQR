@extends('layouts.app')
@section('titulo', 'Recuperar contraseña · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <h2>Recuperar contraseña</h2>
    <p style="color:var(--ink-500);">Te enviaremos un enlace para restablecerla a tu correo institucional</p>

    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="field">
            <label for="email">Correo institucional</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="usuario@univalle.edu" autocomplete="email" required autofocus>
        </div>
        <button type="submit" class="btn">Enviar enlace de recuperación</button>
    </form>

    <p style="margin-top:16px;"><a href="{{ route('login') }}">← Volver a iniciar sesión</a></p>
</div>
@endsection