@extends('layouts.app')

@section('titulo', 'Último paso')

@section('contenido')
    <h1>Último paso</h1>
    <p class="subtitulo">Pedido #{{ $pedido->id }} · revisa y elige cómo pagar.</p>

    <div class="checkout">
        <form id="form-pago" method="POST" action="{{ route('pedido.pagar') }}" novalidate>
            @csrf

            <section class="tarjeta">
                <h2 class="bloque-titulo">¿Cómo quieres pagar?</h2>
                @php($metodo = old('metodo', 'qr'))
                <div class="opciones-pago" role="radiogroup" aria-label="Forma de pago">
                    <label class="opcion-pago">
                        <input type="radio" name="metodo" value="qr" {{ $metodo === 'qr' ? 'checked' : '' }}>
                        <span class="opcion-contenido">
                            <span class="icono">📱</span>
                            <span class="titulo">Pago con QR</span>
                            <span class="detalle">Escanea el código y confirma con el código que te llega al correo.</span>
                        </span>
                    </label>
                    <label class="opcion-pago">
                        <input type="radio" name="metodo" value="efectivo" {{ $metodo === 'efectivo' ? 'checked' : '' }}>
                        <span class="opcion-contenido">
                            <span class="icono">💵</span>
                            <span class="titulo">Efectivo</span>
                            <span class="detalle">Paga en la caja de la cafetería y recibe tu código de recojo.</span>
                        </span>
                    </label>
                </div>
                @error('metodo') <span class="error">{{ $message }}</span> @enderror
            </section>

            <section class="tarjeta">
                <h2 class="bloque-titulo">Datos de retiro</h2>
                <div class="dato-retiro">
                    <span class="icono">🏪</span>
                    <div><strong>{{ config('cafeqr.emisor.nombre') }}</strong><span>{{ config('cafeqr.emisor.direccion') }}</span></div>
                </div>
                <div class="dato-retiro">
                    <span class="icono">🚶</span>
                    <div><strong>Retiro en local</strong><span>Después de pagar, el tiempo de preparación aproximado es de {{ config('cafeqr.tiempo_preparacion') }} min. Te avisamos por correo cuando esté en preparación y cuando esté listo.</span></div>
                </div>
                <div class="dato-retiro">
                    <span class="icono">📧</span>
                    <div><strong>Te avisaremos a</strong><span>{{ $usuario->email }}{{ $usuario->telefono ? ' · '.$usuario->telefono : '' }}</span></div>
                </div>
            </section>

            <section class="tarjeta">
                <h2 class="bloque-titulo">Datos de facturación <span class="opcional" style="font-size:13px;font-weight:400;color:var(--texto-suave)">(opcional)</span></h2>
                <div class="fila-2">
                    <div class="campo {{ $errors->has('razon_social') ? 'con-error' : '' }}">
                        <label for="razon_social">Nombre o razón social</label>
                        <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $pedido->razon_social) }}" maxlength="80" placeholder="{{ $usuario->name }}">
                        @error('razon_social') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="campo {{ $errors->has('nit_ci') ? 'con-error' : '' }}">
                        <label for="nit_ci">NIT o CI</label>
                        <input type="text" id="nit_ci" name="nit_ci" value="{{ old('nit_ci', $pedido->nit_ci) }}" inputmode="numeric" maxlength="13" placeholder="Solo números" data-solo-numeros>
                        @error('nit_ci') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <p class="ayuda">Si lo dejas vacío, la factura sale a tu nombre y con NIT/CI “S/N”.</p>
            </section>
        </form>

        <aside class="resumen-lateral">
            <section class="tarjeta">
                <h2 class="bloque-titulo">Resumen</h2>
                @foreach ($pedido->detalles as $detalle)
                    <div class="resumen-linea">
                        <span>{{ $detalle->cantidad }} × {{ $detalle->nombre }}</span>
                        <span>Bs {{ number_format($detalle->subtotal, 2) }}</span>
                    </div>
                @endforeach
                <div class="resumen-total"><span>Total</span><span>Bs {{ number_format($pedido->total, 2) }}</span></div>

                <button type="submit" form="form-pago" class="btn block" style="margin-top:16px">Pagar Bs {{ number_format($pedido->total, 2) }}</button>
                <p class="centro" style="margin:12px 0 0"><a href="{{ route('pedido.actual') }}">← Modificar pedido</a></p>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    @include('partials.validacion-js')
@endpush
