@extends('layouts.app')

@section('titulo', 'Caja')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Caja · confirmar pagos</h1>
            <p class="subtitulo" style="margin:0">Aquí confirmas los pagos en efectivo y los pagos con QR que el cliente avisó.</p>
        </div>
        <a class="btn gris" href="{{ route('entregas.index') }}">Ir a Entregas →</a>
    </div>

    @if ($modoPrueba)
        <div class="aviso">⚠️ El correo está en <strong>modo prueba</strong> (MAIL_MAILER=log): los clientes NO reciben los mensajes. Configura Gmail en el <code>.env</code> y prueba con <code>php artisan correo:probar tucorreo</code>.</div>
    @endif

    <section class="tarjeta">
        <h2>💵 Cobros en efectivo <span class="etiqueta estado-pendiente_pago">{{ $efectivo->count() }}</span></h2>
        @forelse ($efectivo as $pago)
            @include('caja._pago-fila', ['pago' => $pago, 'textoBoton' => 'Cobrado en efectivo'])
        @empty
            <p class="ayuda">No hay cobros en efectivo pendientes.</p>
        @endforelse
    </section>

    <section class="tarjeta">
        <h2>📱 Pagos con QR por verificar <span class="etiqueta estado-pendiente_pago">{{ $porVerificar->count() }}</span></h2>
        <p class="ayuda">El cliente avisó que ya pagó. Comprueba en tu app que el dinero llegó y confirma: se le envía el código de 6 dígitos a su correo.</p>
        @forelse ($porVerificar as $pago)
            @include('caja._pago-fila', ['pago' => $pago, 'textoBoton' => 'Recibí el dinero'])
        @empty
            <p class="ayuda">No hay pagos por verificar.</p>
        @endforelse
    </section>

    <section class="tarjeta">
        <h2>⏳ Esperando el código del cliente <span class="etiqueta estado-pendiente_pago">{{ $esperandoCodigo->count() }}</span></h2>
        @forelse ($esperandoCodigo as $pago)
            <div class="pedido-card">
                <div class="pedido-cab">
                    <strong>Pedido #{{ $pago->pedido->id }} · {{ $pago->pedido->user->name }}</strong>
                    <span>Bs {{ number_format($pago->monto, 2) }}</span>
                </div>
                <p class="ayuda" style="margin:4px 0 0">Ya se le envió el código al correo. Falta que lo escriba en su pantalla.</p>
            </div>
        @empty
            <p class="ayuda">Ningún cliente está pendiente de escribir su código.</p>
        @endforelse
    </section>
@endsection
