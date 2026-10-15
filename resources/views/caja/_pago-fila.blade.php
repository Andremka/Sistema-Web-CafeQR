<div class="pedido-card">
    <div class="pedido-cab">
        <strong>Pedido #{{ $pago->pedido->id }} · {{ $pago->pedido->user->name }}</strong>
        <strong style="color:var(--guinda)">Bs {{ number_format($pago->monto, 2) }}</strong>
    </div>
    <ul class="pedido-items">
        @foreach ($pago->pedido->detalles as $detalle)
            <li>{{ $detalle->cantidad }} × {{ $detalle->nombre }}</li>
        @endforeach
    </ul>
    <p class="ayuda" style="margin:0">{{ $pago->pedido->user->email }} · {{ $pago->updated_at->timezone(config('app.timezone'))->format('d/m H:i') }}</p>
    <div class="pedido-acciones">
        <form method="POST" action="{{ route('caja.recibido', $pago) }}">
            @csrf
            <button type="submit" class="btn chico verde">{{ $textoBoton }}</button>
        </form>
        <form method="POST" action="{{ route('caja.rechazar', $pago) }}" onsubmit="return confirm('¿Rechazar este pago?');">
            @csrf
            <button type="submit" class="btn chico rojo">Rechazar</button>
        </form>
    </div>
</div>
