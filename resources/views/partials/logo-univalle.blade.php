<div class="sello-univalle">
    @if (is_file(public_path('img/logo-univalle.png')))
        <img class="logo-univalle" src="{{ asset('img/logo-univalle.png') }}" alt="Universidad Privada del Valle">
    @else
        <div class="logo-univalle-texto"><strong>UNIVALLE</strong><span>Universidad Privada del Valle</span></div>
    @endif
</div>
