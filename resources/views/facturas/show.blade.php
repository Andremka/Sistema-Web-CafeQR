@extends('layouts.app')

@section('titulo', 'Factura '.$factura->numero_formateado)

@section('contenido')
    <section class="tarjeta factura">
        @include('facturas._documento')
    </section>

    <div class="acciones no-imprimir" style="justify-content:center">
        <button type="button" class="btn" onclick="window.print()">🖨️ Imprimir / guardar PDF</button>
        <a class="btn gris" href="{{ auth()->user()->esCliente() ? route('pedidos.historial') : route('admin.facturas') }}">Volver</a>
    </div>
@endsection
