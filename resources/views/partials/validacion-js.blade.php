<script>
    // Nombre: no deja escribir números ni símbolos. Celular / NIT: solo números.
    document.querySelectorAll('[data-solo-letras]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.value = campo.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s.'-]/g, '');
        });
    });
    document.querySelectorAll('[data-solo-numeros]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.value = campo.value.replace(/\D/g, '');
        });
    });
</script>
