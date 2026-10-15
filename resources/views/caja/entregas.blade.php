@extends('layouts.app')

@section('titulo', 'Entregas')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Entregas</h1>
            <p class="subtitulo" style="margin:0">Prepara los pedidos, avisa cuándo estarán listos y entrégalos con su código.</p>
        </div>
        <a class="btn gris" href="{{ route('caja.pagos') }}">Ir a Caja →</a>
    </div>

    <form class="buscar-codigo" method="GET" action="{{ route('entregas.index') }}">
        <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar por código de recojo, N.º de pedido o nombre" maxlength="40" aria-label="Buscar pedido">
        <button type="submit" class="btn">Buscar</button>
        @if ($buscar !== '') <a class="btn gris" href="{{ route('entregas.index') }}">Limpiar</a> @endif
    </form>

    <section class="tarjeta">
        <h2>Pedidos por atender <span class="etiqueta estado-pagado">{{ $activos->count() }}</span></h2>

        @forelse ($activos as $pedido)
            <article class="pedido-card {{ $pedido->estado === 'listo' ? 'listo' : '' }}">
                <div class="pedido-cab">
                    <div>
                        <strong>Pedido #{{ $pedido->id }}</strong> · código <span class="codigo">{{ $pedido->codigo }}</span>
                    </div>
                    <span class="etiqueta estado-{{ $pedido->estado }}">{{ $pedido->estado_texto }}</span>
                </div>
                <p class="ayuda" style="margin:4px 0 0">
                    {{ $pedido->user->name }} · {{ $pedido->user->email }}
                    · Pagó {{ $pedido->metodo_pago === 'efectivo' ? 'en efectivo' : 'con QR' }}
                    · {{ $pedido->pagado_en?->timezone(config('app.timezone'))->format('H:i') }}
                </p>
                <ul class="pedido-items">
                    @foreach ($pedido->detalles as $detalle)
                        <li>{{ $detalle->cantidad }} × {{ $detalle->nombre }}</li>
                    @endforeach
                </ul>

                @if ($pedido->estado === 'en_preparacion')
                    <p class="ayuda" style="margin:0">⏱️ Se le dijo al cliente ~{{ $pedido->tiempo_estimado }} min
                        @if ($pedido->minutosRestantes() !== null) · faltan {{ $pedido->minutosRestantes() }} min @endif
                    </p>
                @endif

                <div class="pedido-acciones">
                    @if (in_array($pedido->estado, ['pagado', 'en_preparacion']))
                        <form class="form-mini" method="POST" action="{{ route('entregas.preparar', $pedido) }}">
                            @csrf
                            <input type="number" name="minutos" min="1" max="180" value="{{ $pedido->tiempo_estimado ?? $tiempoPorDefecto }}" aria-label="Minutos de preparación" required>
                            <span class="ayuda">min</span>
                            <button type="submit" class="btn chico">{{ $pedido->estado === 'pagado' ? 'Preparar y avisar' : 'Actualizar tiempo' }}</button>
                        </form>
                        <form method="POST" action="{{ route('entregas.listo', $pedido) }}">
                            @csrf
                            <button type="submit" class="btn chico verde">✓ Marcar listo</button>
                        </form>
                    @endif

                    @if ($pedido->estado === 'listo')
                        <form method="POST" action="{{ route('entregas.entregar', $pedido) }}">
                            @csrf
                            <button type="submit" class="btn chico verde">Entregar al cliente</button>
                        </form>
                    @endif

                    <details class="cancelar-pedido">
                        <summary>Cancelar</summary>
                        <form class="form-mini" method="POST" action="{{ route('entregas.cancelar', $pedido) }}">
                            @csrf
                            <input type="text" name="motivo" placeholder="Motivo (obligatorio)" minlength="5" maxlength="200" required>
                            <button type="submit" class="btn chico rojo">Cancelar pedido</button>
                        </form>
                    </details>
                </div>
            </article>
        @empty
            <div class="vacio">
                <span class="grande">☕</span>
                @if ($buscar !== '') No se encontró ningún pedido con “{{ $buscar }}”. @else No hay pedidos por atender. @endif
            </div>
        @endforelse
    </section>

    @if ($reembolsos->count())
        <section class="tarjeta">
            <h2>💸 Reembolsos pendientes <span class="etiqueta estado-cancelado">{{ $reembolsos->count() }}</span></h2>
            <p class="ayuda">Pedidos cancelados que ya estaban pagados: devuelve el dinero al cliente y márcalo.</p>
            @foreach ($reembolsos as $pedido)
                <div class="pedido-card">
                    <div class="pedido-cab">
                        <strong>Pedido #{{ $pedido->id }} · {{ $pedido->user->name }}</strong>
                        <strong style="color:var(--guinda)">Bs {{ number_format($pedido->total, 2) }}</strong>
                    </div>
                    <p class="ayuda" style="margin:4px 0 0">
                        Pagó {{ $pedido->metodo_pago === 'efectivo' ? 'en efectivo' : 'con QR' }} ·
                        cancelado por {{ $pedido->cancelado_por === 'cliente' ? 'el cliente' : 'la cafetería' }}@if ($pedido->motivo_cancelacion): “{{ $pedido->motivo_cancelacion }}”@endif
                    </p>
                    <div class="pedido-acciones">
                        <form method="POST" action="{{ route('entregas.reembolsar', $pedido) }}">
                            @csrf
                            <button type="submit" class="btn chico verde">Ya devolví el dinero</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <section class="tarjeta">
        <h2>Entregados hoy <span class="etiqueta estado-entregado">{{ $entregadosHoy->count() }}</span></h2>
        @if ($entregadosHoy->count())
            <div class="tabla-wrap">
                <table>
                    <thead><tr><th>Pedido</th><th>Cliente</th><th>Código</th><th class="num">Total</th><th>Hora</th></tr></thead>
                    <tbody>
                        @foreach ($entregadosHoy as $pedido)
                            <tr>
                                <td>#{{ $pedido->id }}</td>
                                <td>{{ $pedido->user->name }}</td>
                                <td><span class="codigo">{{ $pedido->codigo }}</span></td>
                                <td class="num">Bs {{ number_format($pedido->total, 2) }}</td>
                                <td>{{ $pedido->entregado_en?->timezone(config('app.timezone'))->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ayuda">Todavía no se entregó ningún pedido hoy.</p>
        @endif
    </section>
@endsection
