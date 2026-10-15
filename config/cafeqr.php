<?php

return [

    /* Dominio(s) de correo permitidos para que un CLIENTE se registre (separados por coma). */
    'dominio' => env('DOMINIO_INSTITUCIONAL', 'univalle.edu'),

    /* demo = QR de prueba que se escanea con el celular | banco = tu QR de Yape/banco */
    'pago_modo' => env('PAGO_MODO', 'demo'),
    'pago_minutos' => (int) env('PAGO_MINUTOS', 30),

    /* Imagen de tu QR de cobro (modo banco), dentro de la carpeta public */
    'qr_cobro' => env('QR_COBRO', 'img/qr-cobro.png'),
    'banco_nombre' => env('BANCO_NOMBRE', 'Yape'),
    'banco_titular' => env('BANCO_TITULAR', ''),

    /* Si el QR de prueba debe apuntar a otra dirección (por ejemplo http://192.168.1.15:8000). Normalmente se deja vacío. */
    'url_publica' => env('CAFEQR_URL_PUBLICA'),

    /* Minutos de preparación que se proponen por defecto al empleado */
    'tiempo_preparacion' => (int) env('TIEMPO_PREPARACION', 10),

    'emisor' => [
        'nombre' => env('EMISOR_NOMBRE', 'Cafetería Universitaria CafeQR'),
        'nit' => env('EMISOR_NIT', 'S/N (demostración)'),
        'direccion' => env('EMISOR_DIRECCION', 'Universidad Privada del Valle, La Paz - Bolivia'),
    ],

    /* Administrador que se crea con: php artisan db:seed */
    'admin' => [
        'nombre' => env('ADMIN_NOMBRE', 'Administrador CafeQR'),
        'email' => env('ADMIN_EMAIL', 'bmj2031702@est.univalle.edu'),
        'password' => env('ADMIN_PASSWORD', 'Admin12345'),
    ],
];
