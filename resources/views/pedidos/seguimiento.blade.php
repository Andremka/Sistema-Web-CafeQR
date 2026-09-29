@extends('layouts.app')
@section('titulo', 'Seguimiento del pedido · CafeQR')
@section('contenido')
<p><a href="{{ route('menu.index') }}">← Volver al menú</a></p>

<div class="card card-narrow" style="text-align:center;">
    <h2>Seguimiento del pedido</h2>
    <div class="steps">
        <div class="step done">1. Pedido recibido</div>
        <div class="step done">2. Pago confirmado</div>
        <div class="step {{ $pedido->listo ? 'done' : 'active' }}">3. En preparación</div>
        <div class="step {{ $pedido->listo ? 'active' : '' }}">
            4. {{ $pedido->listo ? '¡Listo para recoger! 🔔' : 'Listo para recoger' }}
        </div>
    </div>
    <p>Código de recojo: <strong>{{ $pedido->codigo_recojo }}</strong></p>

    @unless ($pedido->listo)
        <form action="{{ route('pedidos.listo', $pedido) }}" method="POST">
            @csrf
            <button type="submit" class="btn">Simular: Pedido listo</button>
        </form>
    @endunless
</div>
@endsection
