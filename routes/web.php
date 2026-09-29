<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CafeQR — Rutas web
|--------------------------------------------------------------------------
| Agrupadas por módulo, cada grupo corresponde a una o más historias de
| usuario del Product Backlog (ver Parte IV — Aplicación Práctica).
*/

// ---------- Autenticación ----------
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');

    // Recuperación de contraseña
    Route::get('/recuperar-contrasena', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/recuperar-contrasena', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/restablecer-contrasena', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Menú y carrito (HU-05, HU-09) ----------
Route::middleware('auth')->group(function () {
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

    Route::post('/carrito/{producto}/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('/carrito/{producto}/cantidad', [CarritoController::class, 'actualizarCantidad'])->name('carrito.actualizar');
    Route::delete('/carrito/{producto}', [CarritoController::class, 'quitar'])->name('carrito.quitar');

    // ---------- Pago mediante código QR (HU-10) ----------
    Route::get('/pago', [PagoController::class, 'show'])->name('pagos.show');
    Route::post('/pago', [PagoController::class, 'store'])->name('pagos.store');
    Route::post('/pago/cancelar', [PagoController::class, 'cancelar'])->name('pagos.cancelar');
    Route::get('/pago/error', [PagoController::class, 'error'])->name('pagos.error');
    Route::patch('/cuenta-cobro', [PagoController::class, 'actualizarCuentaCobro'])->name('cuenta-cobro.actualizar');

    // ---------- Cupones ----------
    Route::post('/cupones/canjear', [CuponController::class, 'canjear'])->name('cupones.canjear');

    // ---------- Pedidos: confirmación, factura, seguimiento (HU-11 a HU-14) ----------
    Route::get('/pedidos/{pedido}/confirmacion', [PedidoController::class, 'confirmacion'])->name('pedidos.confirmacion');
    Route::get('/pedidos/{pedido}/factura', [PedidoController::class, 'factura'])->name('pedidos.factura');
    Route::post('/pedidos/{pedido}/enviar-codigo', [PedidoController::class, 'enviarCodigo'])->name('pedidos.enviar-codigo');
    Route::get('/pedidos/{pedido}/seguimiento', [PedidoController::class, 'seguimiento'])->name('pedidos.seguimiento');
    Route::post('/pedidos/{pedido}/listo', [PedidoController::class, 'marcarListo'])->name('pedidos.listo');

    // ---------- Mis pedidos (HU-15) ----------
    Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos'])->name('pedidos.mios');
});

// ---------- Panel de administración ----------
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});
