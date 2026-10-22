@extends('layouts.app')

@section('titulo', 'Administración')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Panel del administrador</h1>
            <p class="subtitulo" style="margin:0">Resumen de la cafetería de hoy.</p>
        </div>
        <div class="acciones">
            <a class="btn" href="{{ route('admin.empleado.crear') }}">+ Nuevo empleado</a>
            <a class="btn contorno" href="{{ route('productos.create') }}">+ Nuevo producto</a>
        </div>
    </div>

    @if ($modoPrueba)
        <div class="aviso">⚠️ El correo está en <strong>modo prueba</strong> (MAIL_MAILER=log): los clientes NO reciben los avisos de "en preparación" ni "listo". Configura Gmail en el <code>.env</code> y prueba con <code>php artisan correo:probar tucorreo</code>.</div>
    @endif
    <div class="aviso" style="background:#eef6ff;border-color:#cfe3fb;color:#1e3a5f">
        Modo de pago actual: <strong>{{ $modoPago === 'banco' ? 'Banco (tu QR de '.config('cafeqr.banco_nombre').')' : 'Demostración (QR de prueba)' }}</strong>. Se cambia con <code>PAGO_MODO</code> en el .env.
    </div>

    <div class="kpis">
        <div class="kpi verde-kpi"><div class="num">Bs {{ number_format($ventasHoy, 2) }}</div><div class="rotulo">Ventas de hoy ({{ $pedidosHoy }} {{ $pedidosHoy === 1 ? 'pedido' : 'pedidos' }})</div></div>
        <div class="kpi {{ $porVerificar ? 'alerta-kpi' : '' }}"><div class="num">{{ $porVerificar }}</div><div class="rotulo">Pagos por confirmar en Caja</div></div>
        <div class="kpi"><div class="num">{{ $porEstado['pagado'] ?? 0 }}</div><div class="rotulo">Pagados, sin preparar</div></div>
        <div class="kpi"><div class="num">{{ $porEstado['en_preparacion'] ?? 0 }}</div><div class="rotulo">En preparación</div></div>
        <div class="kpi"><div class="num">{{ $porEstado['listo'] ?? 0 }}</div><div class="rotulo">Listos para entregar</div></div>
        <div class="kpi {{ $reembolsos ? 'alerta-kpi' : '' }}"><div class="num">{{ $reembolsos }}</div><div class="rotulo">Reembolsos pendientes</div></div>
        <a class="kpi {{ $correosFallidos ? 'alerta-kpi' : '' }}" href="{{ route('admin.correos', $correosFallidos ? ['estado' => 'fallido'] : []) }}" style="text-decoration:none;color:inherit"><div class="num">{{ $correosFallidos }}</div><div class="rotulo">Correos que no se enviaron (7 días)</div></a>
    </div>

    <div class="columnas">
        <section class="tarjeta">
            <h2>Gestión</h2>
            <div class="acciones" style="margin-top:0">
                <a class="btn contorno" href="{{ route('entregas.index') }}">Entregas</a>
                <a class="btn contorno" href="{{ route('caja.pagos') }}">Caja</a>
                <a class="btn contorno" href="{{ route('productos.index') }}">Productos</a>
                <a class="btn contorno" href="{{ route('admin.pedidos') }}">Todos los pedidos</a>
                <a class="btn contorno" href="{{ route('admin.facturas') }}">Facturas</a>
                <a class="btn contorno" href="{{ route('admin.usuarios') }}">Usuarios</a>
                <a class="btn contorno" href="{{ route('admin.correos') }}">Correos</a>
            </div>
        </section>

        <section class="tarjeta">
            <h2>Totales</h2>
            <div class="resumen-linea"><span>Clientes registrados</span><strong>{{ $clientes }}</strong></div>
            <div class="resumen-linea"><span>Empleados</span><strong>{{ $empleados }}</strong></div>
            <div class="resumen-linea"><span>Productos en el menú</span><strong>{{ $productos }} ({{ $agotados }} agotados)</strong></div>
            <div class="resumen-linea"><span>Facturas emitidas</span><strong>{{ $facturas }}</strong></div>
            <div class="resumen-linea"><span>Total facturado</span><strong>Bs {{ number_format($totalFacturado, 2) }}</strong></div>
        </section>
    </div>
@endsection
