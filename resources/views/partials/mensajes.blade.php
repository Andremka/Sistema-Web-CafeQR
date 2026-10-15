@if (session('exito'))
    <div class="exito" role="status">{{ session('exito') }}</div>
@endif
@if (session('error'))
    <div class="alerta" role="alert">{{ session('error') }}</div>
@endif
