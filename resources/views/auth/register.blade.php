@extends('layouts.app')
@section('titulo', 'Crear cuenta · CafeQR')
@section('contenido')
<div class="card card-narrow" style="text-align:center;">
    <h2>Crear cuenta</h2>
    <p style="color:var(--ink-500);">Regístrate con tu correo institucional</p>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error) {{ $error }}<br> @endforeach
        </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST">
        @csrf
        <div class="field">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nombre y apellido">
        </div>
        <div class="field">
            <label for="email">Correo institucional</label>
            <input type="text" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@univalle.edu">
        </div>
        <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="••••••••">
        </div>
        <button type="submit" class="btn">Crear cuenta</button>
    </form>

    <p style="margin-top:16px;">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div>
@endsection
