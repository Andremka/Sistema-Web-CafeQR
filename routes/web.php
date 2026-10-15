<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->rutaInicio())
        : redirect()->route('login');
});

/* ---- QR de prueba: se abre desde el celular, sin iniciar sesión ---- */
Route::get('/pagar/{referencia}', [PagoController::class, 'escanear'])->where('referencia', '[A-Za-z0-9]+')->name('pagar.escanear');
Route::post('/pagar/{referencia}', [PagoController::class, 'ejecutar'])->where('referencia', '[A-Za-z0-9]+')->name('pagar.ejecutar');

/* ---- Visitantes ---- */
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrar'])->name('registro.guardar');
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'entrar'])->name('login.entrar');

    Route::get('/recuperar', [PasswordResetController::class, 'mostrarSolicitud'])->name('password.request');
    Route::post('/recuperar', [PasswordResetController::class, 'enviarEnlace'])->name('password.email');
    Route::get('/restablecer/{token}', [PasswordResetController::class, 'mostrarRestablecer'])->name('password.reset');
    Route::post('/restablecer', [PasswordResetController::class, 'restablecer'])->name('password.update');
});

/* ---- Cualquier usuario con sesión ---- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'salir'])->name('logout');

    Route::get('/dashboard', [MenuController::class, 'index'])->name('dashboard');

    Route::get('/perfil', [PerfilController::class, 'mostrar'])->name('perfil.show');
    Route::post('/perfil', [PerfilController::class, 'actualizar'])->name('perfil.update');
    Route::post('/perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');

    Route::get('/factura/{pedido}', [FacturaController::class, 'mostrar'])->name('factura.show');

    /* ---- Clientes: ver el menú y pedir ---- */
    Route::middleware('rol:cliente')->group(function () {
        Route::get('/mi-pedido', [PedidoController::class, 'actual'])->name('pedido.actual');
        Route::post('/mi-pedido/agregar/{producto}', [PedidoController::class, 'agregar'])->name('pedido.agregar');
        Route::patch('/mi-pedido/detalle/{detalle}', [PedidoController::class, 'cambiar'])->name('pedido.cambiar');
        Route::delete('/mi-pedido/detalle/{detalle}', [PedidoController::class, 'quitar'])->name('pedido.quitar');
        Route::get('/mi-pedido/confirmar', [PedidoController::class, 'confirmar'])->name('pedido.confirmar');
        Route::post('/mi-pedido/pagar', [PedidoController::class, 'pagar'])->name('pedido.pagar');
        Route::get('/mi-pedido/continuar', [PedidoController::class, 'continuar'])->name('pedido.continuar');
        Route::post('/mi-pedido/modificar', [PedidoController::class, 'modificar'])->name('pedido.modificar');
        Route::get('/mis-pedidos', [PedidoController::class, 'historial'])->name('pedidos.historial');

        Route::get('/pedido/{pedido}/gracias', [PedidoController::class, 'exito'])->name('pedido.exito');
        Route::get('/pedido/{pedido}/seguimiento', [SeguimientoController::class, 'mostrar'])->name('pedido.seguimiento');
        Route::get('/pedido/{pedido}/estado', [SeguimientoController::class, 'estado'])->name('pedido.estado');
        Route::post('/pedido/{pedido}/cancelar', [SeguimientoController::class, 'cancelar'])->name('pedido.cancelar');

        Route::get('/pago/{pago}', [PagoController::class, 'mostrar'])->name('pago.show');
        Route::get('/pago/{pago}/estado', [PagoController::class, 'estado'])->name('pago.estado');
        Route::post('/pago/{pago}/reintentar', [PagoController::class, 'reintentar'])->name('pago.reintentar');
        Route::post('/pago/{pago}/ya-pague', [PagoController::class, 'yaPague'])->name('pago.ya-pague');
        Route::post('/pago/{pago}/simular', [PagoController::class, 'simular'])->name('pago.simular');
        Route::get('/pago/{pago}/codigo', [PagoController::class, 'codigo'])->name('pago.codigo');
        Route::post('/pago/{pago}/codigo', [PagoController::class, 'comprobarCodigo'])->name('pago.codigo.comprobar');
        Route::post('/pago/{pago}/reenviar', [PagoController::class, 'reenviarCodigo'])->name('pago.reenviar');
    });

    /* ---- Empleados y administrador: productos, caja y entregas ---- */
    Route::middleware('rol:empleado,administrador')->group(function () {
        Route::get('/gestion/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/gestion/productos/nuevo', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/gestion/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('/gestion/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/gestion/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::delete('/gestion/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        Route::post('/gestion/productos/{producto}/disponibilidad', [ProductoController::class, 'alternar'])->name('productos.alternar');

        Route::get('/caja/pagos', [CajaController::class, 'pagos'])->name('caja.pagos');
        Route::post('/caja/pagos/{pago}/recibido', [CajaController::class, 'recibido'])->name('caja.recibido');
        Route::post('/caja/pagos/{pago}/rechazar', [CajaController::class, 'rechazar'])->name('caja.rechazar');

        Route::get('/caja/entregas', [EntregaController::class, 'index'])->name('entregas.index');
        Route::post('/caja/entregas/{pedido}/preparar', [EntregaController::class, 'preparar'])->name('entregas.preparar');
        Route::post('/caja/entregas/{pedido}/listo', [EntregaController::class, 'listo'])->name('entregas.listo');
        Route::post('/caja/entregas/{pedido}/entregar', [EntregaController::class, 'entregar'])->name('entregas.entregar');
        Route::post('/caja/entregas/{pedido}/cancelar', [EntregaController::class, 'cancelar'])->name('entregas.cancelar');
        Route::post('/caja/entregas/{pedido}/reembolsar', [EntregaController::class, 'reembolsar'])->name('entregas.reembolsar');
    });

    /* ---- Solo administrador ---- */
    Route::middleware('rol:administrador')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'panel'])->name('admin.panel');
        Route::get('/pedidos', [AdminController::class, 'pedidos'])->name('admin.pedidos');
        Route::get('/facturas', [AdminController::class, 'facturas'])->name('admin.facturas');
        Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('admin.usuarios');
        Route::get('/usuarios/empleado/nuevo', [AdminController::class, 'crearEmpleado'])->name('admin.empleado.crear');
        Route::post('/usuarios/empleado', [AdminController::class, 'guardarEmpleado'])->name('admin.empleado.guardar');
        Route::post('/usuarios/{usuario}/rol', [AdminController::class, 'cambiarRol'])->name('admin.usuarios.rol');
        Route::delete('/usuarios/{usuario}', [AdminController::class, 'eliminar'])->name('admin.usuarios.eliminar');
    });
});
