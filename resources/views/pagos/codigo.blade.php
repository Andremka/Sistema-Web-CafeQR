@extends('layouts.app')

@section('titulo', 'Confirma tu pago')
@section('ancho', 'angosto')

@section('contenido')
    <div class="pasos-pago">
        <span class="hecho">1 · Pedido</span>
        <span class="hecho">2 · Pago</span>
        <span class="activo">3 · Código del correo</span>
        <span>4 · Listo</span>
    </div>

    <section class="tarjeta verificar">
        <div class="icono-correo">📧</div>
        <h1>Revisa tu correo</h1>
        <p class="subtitulo">Recibimos tu pago de <strong>Bs {{ number_format($pago->monto, 2) }}</strong>. Te enviamos un código de 6 dígitos a <strong>{{ $correo }}</strong>. Vale por {{ \App\Models\Verificacion::MINUTOS }} minutos.</p>

        @if ($modoPrueba)
            <div class="aviso" style="text-align:left">
                <strong>Modo prueba:</strong> el correo no sale a internet (MAIL_MAILER=log). El código está en <code>storage/logs/laravel.log</code>.
                Para recibirlo de verdad, configura Gmail en el archivo <code>.env</code>.
            </div>
        @endif

        <form method="POST" action="{{ route('pago.codigo.comprobar', $pago) }}" novalidate>
            @csrf
            <div class="campo {{ $errors->has('codigo') ? 'con-error' : '' }}">
                <label for="codigo" class="oculto">Código de 6 dígitos</label>
                <input class="input-codigo" type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code"
                       maxlength="6" placeholder="······" required autofocus data-solo-numeros>
                @error('codigo') <span class="error">{{ $message }}</span> @enderror
            </div>
            <p class="ayuda">Intentos disponibles: {{ $intentos }}</p>
            <button type="submit" class="btn block">Confirmar compra</button>
        </form>

        <form method="POST" action="{{ route('pago.reenviar', $pago) }}" style="margin-top:14px">
            @csrf
            <button type="submit" class="btn contorno">Reenviar código</button>
        </form>
    </section>

    <section class="tarjeta">
        <details class="cancelar-pedido">
            <summary>No quiero continuar: cancelar pedido</summary>
            <p class="ayuda">Como ya recibimos tu dinero, quedará un reembolso pendiente que te devuelven en la cafetería.</p>
            <form method="POST" action="{{ route('pedido.cancelar', $pedido) }}">
                @csrf
                <div class="campo">
                    <label for="motivo">Motivo <span class="opcional">(opcional)</span></label>
                    <textarea id="motivo" name="motivo" maxlength="200"></textarea>
                </div>
                <button type="submit" class="btn rojo">Sí, cancelar pedido</button>
            </form>
        </details>
    </section>
@endsection

@push('scripts')
    @include('partials.validacion-js')
@endpush
