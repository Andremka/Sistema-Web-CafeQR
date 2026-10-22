<?php

namespace Tests\Feature;

use App\Mail\CafeQrMail;
use App\Models\Correo;
use App\Services\Notificador;
use Illuminate\Support\Facades\URL;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CuponSeeder;
use Database\Seeders\ProductoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CafeQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cafeqr.pago_modo' => 'demo', 'cafeqr.dominio' => 'univalle.edu']);
        $this->seed(AdminSeeder::class);
        $this->seed(ProductoSeeder::class);
        $this->seed(CuponSeeder::class);
        Mail::fake();
    }

    private function usuario(string $rol = 'cliente', string $correo = null): User
    {
        static $n = 0;
        $n++;

        return User::create([
            'name' => 'Persona Prueba',
            'email' => $correo ?? "persona{$n}@est.univalle.edu",
            'password' => 'Clave12345',
            'rol' => $rol,
        ]);
    }

    private function admin(): User
    {
        return User::where('rol', 'administrador')->firstOrFail();
    }

    /** Cliente con un pedido ya confirmado hacia el método indicado. Devuelve [cliente, pago]. */
    private function pedidoHastaPago(string $metodo): array
    {
        $cliente = $this->usuario();
        $producto = Producto::where('disponible', true)->first();

        $this->actingAs($cliente)->post(route('pedido.agregar', $producto), ['cantidad' => 2])->assertSessionHasNoErrors();
        $this->actingAs($cliente)->get(route('pedido.confirmar'))->assertOk()->assertSee('Último paso');
        $this->actingAs($cliente)->post(route('pedido.pagar'), [
            'metodo' => $metodo, 'razon_social' => 'Juan Perez', 'nit_ci' => '1234567',
        ])->assertRedirect();

        $pago = Pago::latest('id')->firstOrFail();

        return [$cliente, $pago];
    }

    /** Lee el código de 6 dígitos del último correo de verificación enviado. */
    private function codigoEnviado(): string
    {
        $codigos = Mail::sent(CafeQrMail::class)
            ->filter(fn ($m) => isset($m->datos['codigo']))
            ->map(fn ($m) => $m->datos['codigo']);

        $this->assertNotEmpty($codigos, 'No se envió el correo con el código');

        return (string) $codigos->last();
    }

    /* ------------------------------ Registro ------------------------------ */

    public function test_registro_valido_crea_cliente(): void
    {
        $this->post(route('registro.guardar'), [
            'name' => 'María José Peña', 'email' => 'maria@est.univalle.edu', 'telefono' => '71234567',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasNoErrors();

        $this->assertSame('cliente', User::where('email', 'maria@est.univalle.edu')->value('rol'));
        $this->assertAuthenticated();
    }

    public function test_el_nombre_no_acepta_numeros(): void
    {
        foreach (['Juan123', '12345', 'Ana 2', 'J@n', 'x'] as $nombre) {
            $this->post(route('registro.guardar'), [
                'name' => $nombre, 'email' => 'a'.md5($nombre).'@est.univalle.edu',
                'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
            ])->assertSessionHasErrors('name');
        }
        $this->assertGuest();
    }

    public function test_registro_valida_correo_telefono_y_clave(): void
    {
        $base = ['name' => 'Ana Lopez', 'email' => 'ana@est.univalle.edu', 'password' => 'Clave12345', 'password_confirmation' => 'Clave12345'];

        $this->post(route('registro.guardar'), ['email' => 'ana@gmail.com'] + $base)->assertSessionHasErrors('email');
        $this->post(route('registro.guardar'), ['email' => 'no-es-correo'] + $base)->assertSessionHasErrors('email');
        $this->post(route('registro.guardar'), ['telefono' => '12345678'] + $base)->assertSessionHasErrors('telefono');
        $this->post(route('registro.guardar'), ['telefono' => 'abc'] + $base)->assertSessionHasErrors('telefono');
        $this->post(route('registro.guardar'), ['password' => 'corta1', 'password_confirmation' => 'corta1'] + $base)->assertSessionHasErrors('password');
        $this->post(route('registro.guardar'), ['password' => 'sololetras', 'password_confirmation' => 'sololetras'] + $base)->assertSessionHasErrors('password');
        $this->post(route('registro.guardar'), ['password_confirmation' => 'otra'] + $base)->assertSessionHasErrors('password');
    }

    public function test_correo_repetido_no_se_registra(): void
    {
        $this->usuario('cliente', 'repe@est.univalle.edu');
        $this->post(route('registro.guardar'), [
            'name' => 'Otra Persona', 'email' => 'repe@est.univalle.edu',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasErrors('email');
    }

    public function test_login_correcto_e_incorrecto(): void
    {
        $this->usuario('cliente', 'login@est.univalle.edu');
        $this->post(route('login.entrar'), ['email' => 'login@est.univalle.edu', 'password' => 'mala'])->assertSessionHasErrors();
        $this->assertGuest();
        $this->post(route('login.entrar'), ['email' => 'login@est.univalle.edu', 'password' => 'Clave12345'])->assertRedirect();
        $this->assertAuthenticated();
    }

    /* ------------------------------ Roles ------------------------------ */

    public function test_el_admin_del_seeder_existe_y_entra(): void
    {
        $admin = $this->admin();
        $this->assertSame(config('cafeqr.admin.email'), $admin->email);
        $this->post(route('login.entrar'), ['email' => $admin->email, 'password' => config('cafeqr.admin.password')]);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_cliente_solo_ve_menu_y_pide(): void
    {
        $cliente = $this->usuario();
        $this->actingAs($cliente)->get(route('dashboard'))->assertOk();
        $this->actingAs($cliente)->get(route('pedidos.historial'))->assertOk();
    }

    public function test_cliente_no_entra_a_gestion(): void
    {
        $cliente = $this->usuario();
        foreach (['productos.index', 'productos.create', 'caja.pagos', 'entregas.index', 'admin.panel', 'admin.usuarios', 'admin.facturas', 'admin.pedidos'] as $ruta) {
            $this->actingAs($cliente)->get(route($ruta))->assertRedirect(route('dashboard'));
        }
    }

    public function test_empleado_gestiona_pero_no_es_admin(): void
    {
        $empleado = $this->usuario('empleado');
        foreach (['productos.index', 'productos.create', 'caja.pagos', 'entregas.index'] as $ruta) {
            $this->actingAs($empleado)->get(route($ruta))->assertOk();
        }
        foreach (['admin.panel', 'admin.usuarios', 'admin.facturas', 'admin.empleado.crear'] as $ruta) {
            $this->actingAs($empleado)->get(route($ruta))->assertRedirect();
        }
        $this->actingAs($empleado)->get(route('pedido.actual'))->assertRedirect();
    }

    public function test_admin_ve_todo(): void
    {
        $admin = $this->admin();
        foreach (['admin.panel', 'admin.pedidos', 'admin.facturas', 'admin.usuarios', 'admin.empleado.crear',
            'productos.index', 'productos.create', 'caja.pagos', 'entregas.index'] as $ruta) {
            $this->actingAs($admin)->get(route($ruta))->assertOk();
        }
    }

    public function test_invitado_va_al_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.panel'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk();
        $this->get(route('registro'))->assertOk();
    }

    /* ------------------------------ Admin: empleados ------------------------------ */

    public function test_admin_crea_y_elimina_empleado(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.empleado.guardar'), [
            'name' => 'Pedro Empleado', 'email' => 'pedro@cafeqr.test', 'telefono' => '61234567',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasNoErrors();

        $empleado = User::where('email', 'pedro@cafeqr.test')->firstOrFail();
        $this->assertSame('empleado', $empleado->rol);

        $this->actingAs($admin)->post(route('admin.empleado.guardar'), [
            'name' => 'Pedro 99', 'email' => 'p2@cafeqr.test',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasErrors('name');

        $this->actingAs($admin)->delete(route('admin.usuarios.eliminar', $empleado))->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'pedro@cafeqr.test']);
    }

    public function test_admin_cancela_pedidos_y_elimina_cliente_con_pedidos(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('efectivo');
        $pedido = $pago->pedido;
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.pedidos'))->assertOk()->assertSee('Cancelar');
        $this->actingAs($admin)->post(route('entregas.cancelar', $pedido), ['motivo' => 'ab'])->assertSessionHasErrors('motivo');
        $this->actingAs($admin)->post(route('entregas.cancelar', $pedido), ['motivo' => 'Prueba del administrador'])->assertSessionHasNoErrors();
        $this->assertSame(Pedido::CANCELADO, $pedido->fresh()->estado);
        Mail::assertSent(CafeQrMail::class, fn ($m) => $m->hasTo($cliente->email) && str_contains($m->asunto, 'cancelado'));

        $this->actingAs($admin)->get(route('admin.usuarios'))->assertOk();
        $this->actingAs($admin)->delete(route('admin.usuarios.eliminar', $cliente))->assertSessionHas('exito');
        $this->assertDatabaseMissing('users', ['id' => $cliente->id]);
        $this->assertDatabaseMissing('pedidos', ['id' => $pedido->id]);
        $this->assertDatabaseMissing('pagos', ['id' => $pago->id]);

        // puede volver a registrarse con el mismo correo
        auth()->logout();
        $this->post(route('registro.guardar'), [
            'name' => 'Persona Prueba', 'email' => $cliente->email,
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_no_puede_eliminarse_ni_cambiarse_el_rol(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.usuarios.eliminar', $admin));
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->actingAs($admin)->post(route('admin.usuarios.rol', $admin), ['rol' => 'cliente']);
        $this->assertSame('administrador', $admin->fresh()->rol);
    }

    /* ------------------------------ Productos ------------------------------ */

    public function test_empleado_crea_edita_oculta_y_elimina_productos(): void
    {
        $empleado = $this->usuario('empleado');

        $this->actingAs($empleado)->post(route('productos.store'), [
            'nombre' => 'Café especial', 'precio' => '12.50', 'categoria' => array_key_first(Producto::CATEGORIAS), 'disponible' => '1',
        ])->assertSessionHasNoErrors();
        $producto = Producto::where('nombre', 'Café especial')->firstOrFail();

        $this->actingAs($empleado)->post(route('productos.store'), [
            'nombre' => 'X', 'precio' => '-3', 'categoria' => 'nada',
        ])->assertSessionHasErrors(['nombre', 'precio', 'categoria']);

        $this->actingAs($empleado)->put(route('productos.update', $producto), [
            'nombre' => 'Café especial', 'precio' => '15', 'categoria' => $producto->categoria, 'disponible' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertEquals(15, $producto->fresh()->precio);

        $antes = $producto->fresh()->disponible;
        $this->actingAs($empleado)->post(route('productos.alternar', $producto))->assertRedirect();
        $this->assertNotSame($antes, $producto->fresh()->disponible);

        $this->actingAs($empleado)->delete(route('productos.destroy', $producto))->assertRedirect();
        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }

    public function test_producto_agotado_no_se_puede_pedir(): void
    {
        $cliente = $this->usuario();
        $producto = Producto::first();
        $producto->update(['disponible' => false]);
        $this->actingAs($cliente)->post(route('pedido.agregar', $producto))->assertSessionHas('error');
        $this->assertSame(0, Pedido::count());
    }

    /* ------------------------------ Pago con QR ------------------------------ */

    public function test_la_pantalla_del_qr_muestra_un_qr_con_enlace_publico(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('qr');

        $resp = $this->actingAs($cliente)->get(route('pago.show', $pago))->assertOk();
        $resp->assertSee('data:image/svg+xml;base64', false);
        $resp->assertSee('/pagar/'.$pago->referencia, false);
    }

    public function test_la_pagina_publica_del_qr_funciona_sin_iniciar_sesion(): void
    {
        [, $pago] = $this->pedidoHastaPago('qr');
        auth()->logout();

        $this->get(route('pagar.escanear', $pago->referencia))->assertOk();
        $this->post(route('pagar.ejecutar', $pago->referencia))->assertRedirect();
        $this->assertSame('recibido', $pago->fresh()->estado);
        $this->get('/pagar/NOEXISTE')->assertNotFound();
    }

    public function test_flujo_completo_qr_codigo_factura_preparacion_y_listo(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('qr');
        $pedido = $pago->pedido;

        // El cliente paga con el QR (o con el botón de simular)
        $this->actingAs($cliente)->post(route('pago.simular', $pago))->assertRedirect(route('pago.codigo', $pago));
        $this->assertSame('recibido', $pago->fresh()->estado);
        $this->assertSame(Pedido::PENDIENTE_PAGO, $pedido->fresh()->estado);

        // Código incorrecto y luego correcto
        $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => '000000'])->assertSessionHasErrors('codigo');
        $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => 'abc'])->assertSessionHasErrors('codigo');
        $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => $this->codigoEnviado()])
            ->assertRedirect(route('pedido.exito', $pedido));

        $pedido = $pedido->fresh();
        $this->assertSame(Pedido::PAGADO, $pedido->estado);
        $this->assertNotEmpty($pedido->codigo);
        $this->assertNotNull($pedido->factura);
        $this->actingAs($cliente)->get(route('pedido.exito', $pedido))->assertOk()->assertSee($pedido->codigo);
        $this->actingAs($cliente)->get(route('factura.show', $pedido))->assertOk();

        // Empleado: en preparación con tiempo -> correo al cliente
        $empleado = $this->usuario('empleado');
        $this->actingAs($empleado)->post(route('entregas.preparar', $pedido), ['minutos' => 0])->assertSessionHasErrors('minutos');
        $this->actingAs($empleado)->post(route('entregas.preparar', $pedido), ['minutos' => 15])->assertSessionHasNoErrors();
        $this->assertSame(Pedido::EN_PREPARACION, $pedido->fresh()->estado);
        $this->assertSame(15, (int) $pedido->fresh()->tiempo_estimado);
        Mail::assertSent(CafeQrMail::class, fn ($m) => $m->hasTo($cliente->email) && str_contains($m->asunto, 'en preparación') && ($m->datos['minutos'] ?? null) == 15);

        // El cliente ve el tiempo en el seguimiento
        $this->actingAs($cliente)->get(route('pedido.seguimiento', $pedido))->assertOk()->assertSee('15');
        $this->actingAs($cliente)->get(route('pedido.estado', $pedido))->assertOk()->assertJsonFragment(['estado' => Pedido::EN_PREPARACION]);

        // Listo -> correo
        $this->actingAs($empleado)->post(route('entregas.listo', $pedido))->assertSessionHasNoErrors();
        $this->assertSame(Pedido::LISTO, $pedido->fresh()->estado);
        Mail::assertSent(CafeQrMail::class, fn ($m) => $m->hasTo($cliente->email) && str_contains($m->asunto, 'listo'));

        // Entrega
        $this->actingAs($empleado)->post(route('entregas.entregar', $pedido))->assertSessionHasNoErrors();
        $this->assertSame(Pedido::ENTREGADO, $pedido->fresh()->estado);

        // El admin ve la factura
        $this->actingAs($this->admin())->get(route('admin.facturas'))->assertOk()->assertSee('Juan Perez');
    }

    public function test_codigo_se_bloquea_tras_demasiados_intentos(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('qr');
        $this->actingAs($cliente)->post(route('pago.simular', $pago));
        $correcto = $this->codigoEnviado();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => '111111']);
        }
        $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => $correcto])->assertSessionHasErrors('codigo');
        $this->assertNotSame(Pedido::PAGADO, $pago->pedido->fresh()->estado);
    }

    public function test_cancelar_con_pago_pendiente(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('qr');
        $pedido = $pago->pedido;

        $this->actingAs($cliente)->post(route('pedido.cancelar', $pedido))->assertRedirect();
        $this->assertSame(Pedido::CANCELADO, $pedido->fresh()->estado);
        $this->assertSame('cancelado', $pago->fresh()->estado);
        $this->assertFalse((bool) $pedido->fresh()->reembolso_pendiente);
    }

    public function test_cancelar_despues_de_pagar_deja_reembolso_pendiente(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('qr');
        $this->actingAs($cliente)->post(route('pago.simular', $pago));
        $this->actingAs($cliente)->post(route('pago.codigo.comprobar', $pago), ['codigo' => $this->codigoEnviado()]);
        $pedido = $pago->pedido->fresh();
        $this->assertSame(Pedido::PAGADO, $pedido->estado);

        $this->actingAs($cliente)->post(route('pedido.cancelar', $pedido))->assertRedirect();
        $this->assertSame(Pedido::CANCELADO, $pedido->fresh()->estado);
        $this->assertTrue((bool) $pedido->fresh()->reembolso_pendiente);

        $empleado = $this->usuario('empleado');
        $this->actingAs($empleado)->get(route('entregas.index'))->assertOk();
        $this->actingAs($empleado)->post(route('entregas.reembolsar', $pedido))->assertSessionHasNoErrors();
        $this->assertFalse((bool) $pedido->fresh()->reembolso_pendiente);
    }

    public function test_cliente_no_ve_pagos_ni_pedidos_de_otro(): void
    {
        [, $pago] = $this->pedidoHastaPago('qr');
        $otro = $this->usuario();
        $this->actingAs($otro)->get(route('pago.show', $pago))->assertForbidden();
        $this->actingAs($otro)->get(route('pedido.seguimiento', $pago->pedido))->assertForbidden();
        $this->actingAs($otro)->post(route('pedido.cancelar', $pago->pedido))->assertForbidden();
    }

    /* ------------------------------ Efectivo ------------------------------ */

    public function test_flujo_efectivo_lo_confirma_el_empleado_en_caja(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('efectivo');
        $pedido = $pago->pedido;

        $this->actingAs($cliente)->get(route('pago.show', $pago))->assertOk();

        $empleado = $this->usuario('empleado');
        $this->actingAs($empleado)->get(route('caja.pagos'))->assertOk();
        $this->actingAs($empleado)->post(route('caja.recibido', $pago))->assertSessionHasNoErrors();

        $pedido = $pedido->fresh();
        $this->assertSame(Pedido::PAGADO, $pedido->estado);
        $this->assertNotNull($pedido->factura);
        $this->assertNotEmpty($pedido->codigo);
        Mail::assertSent(CafeQrMail::class, fn ($m) => $m->hasTo($cliente->email));
    }

    public function test_cliente_puede_modificar_pedido_pendiente(): void
    {
        [$cliente, $pago] = $this->pedidoHastaPago('efectivo');
        $this->actingAs($cliente)->post(route('pedido.modificar'))->assertRedirect(route('pedido.actual'));
        $this->assertSame(Pedido::CARRITO, $pago->pedido->fresh()->estado);
    }

    /* ------------------------------ Facturación ------------------------------ */

    public function test_nit_y_razon_social_se_validan(): void
    {
        $cliente = $this->usuario();
        $this->actingAs($cliente)->post(route('pedido.agregar', Producto::first()));
        $this->actingAs($cliente)->post(route('pedido.pagar'), ['metodo' => 'qr', 'nit_ci' => 'abc'])->assertSessionHasErrors('nit_ci');
        $this->actingAs($cliente)->post(route('pedido.pagar'), ['metodo' => 'bitcoin'])->assertSessionHasErrors('metodo');
        $this->actingAs($cliente)->post(route('pedido.pagar'), [])->assertSessionHasErrors('metodo');
    }

    public function test_foto_suelta_en_public_productos_se_detecta_sola(): void
    {
        $producto = Producto::create(['nombre' => 'Café Fotogénico', 'descripcion' => 'x', 'precio' => 5, 'categoria' => 'Snacks', 'disponible' => true]);
        $this->assertNull($producto->imagen_url);

        $ruta = public_path('productos/cafe-fotogenico.png');
        @mkdir(dirname($ruta), 0777, true);
        file_put_contents($ruta, 'x');
        try {
            $this->assertStringEndsWith('productos/cafe-fotogenico.png', $producto->fresh()->imagen_url);
        } finally {
            @unlink($ruta);
        }
    }

    /* ------------------------------ Cupones (aporte de main) ------------------------------ */

    public function test_cupon_valido_descuenta_y_se_ve_en_el_pago(): void
    {
        $cliente = $this->usuario();
        $producto = Producto::where('disponible', true)->where('precio', '>=', 8)->first();
        $this->actingAs($cliente)->post(route('pedido.agregar', $producto), ['cantidad' => 3]); // más de Bs 20
        $subtotal = 3 * (float) $producto->precio;

        $this->actingAs($cliente)->post(route('pedido.cupon'), ['cupon' => ' univalle10 '])->assertSessionHas('exito');
        $pedido = $cliente->pedidos()->latest('id')->first();
        $this->assertSame('UNIVALLE10', $pedido->cupon);
        $this->assertEqualsWithDelta($subtotal - 10, (float) $pedido->total, 0.001);

        $this->actingAs($cliente)->post(route('pedido.pagar'), ['metodo' => 'qr'])->assertRedirect();
        $this->assertEqualsWithDelta($subtotal - 10, (float) Pago::latest('id')->first()->monto, 0.001);
    }

    public function test_cupon_invalido_o_muy_grande_se_rechaza(): void
    {
        $cliente = $this->usuario();
        $barato = Producto::where('disponible', true)->orderBy('precio')->first(); // Bs 4 a 5
        $this->actingAs($cliente)->post(route('pedido.agregar', $barato));

        $this->actingAs($cliente)->post(route('pedido.cupon'), ['cupon' => 'NOEXISTE'])->assertSessionHas('error');
        $this->actingAs($cliente)->post(route('pedido.cupon'), ['cupon' => 'CAFEQR20'])->assertSessionHas('error');
        $this->actingAs($cliente)->post(route('pedido.cupon'), [])->assertSessionHasErrors('cupon');
        $this->assertNull($cliente->pedidos()->latest('id')->first()->cupon);
    }

    public function test_cupon_se_quita_solo_si_el_pedido_baja_del_monto(): void
    {
        $cliente = $this->usuario();
        $caro = Producto::where('disponible', true)->where('precio', '>=', 12)->first();
        $this->actingAs($cliente)->post(route('pedido.agregar', $caro), ['cantidad' => 1]);
        $this->actingAs($cliente)->post(route('pedido.agregar', $caro), ['cantidad' => 1]); // 2 unidades
        $this->actingAs($cliente)->post(route('pedido.cupon'), ['cupon' => 'UNIVALLE10']);
        $pedido = $cliente->pedidos()->latest('id')->first();
        $this->assertSame('UNIVALLE10', $pedido->fresh()->cupon);

        $detalle = $pedido->detalles()->first();
        $this->actingAs($cliente)->patch(route('pedido.cambiar', $detalle), ['cantidad' => 1]);
        $pedido->refresh();
        if ((float) $caro->precio <= 10) {
            $this->assertNull($pedido->cupon);
        } else {
            $this->assertEqualsWithDelta((float) $caro->precio - 10, (float) $pedido->total, 0.001);
        }
        $this->actingAs($cliente)->delete(route('pedido.cupon.quitar'));
        $this->assertNull($pedido->fresh()->cupon);
        $this->assertEqualsWithDelta((float) $caro->precio, (float) $pedido->fresh()->total, 0.001);
    }

    public function test_menu_muestra_la_categoria_combos(): void
    {
        $this->actingAs($this->usuario())->get(route('dashboard'))->assertOk()->assertSee('Combo ejecutivo')->assertSee('Combos');
    }

    /* ------------------------------ Correos (bienvenida, recuperar, historial) ------------------------------ */

    private function correosDe(string $vista)
    {
        return Mail::sent(CafeQrMail::class)->filter(fn ($m) => $m->vistaCorreo === $vista);
    }

    public function test_registro_envia_correo_de_bienvenida_y_lo_guarda(): void
    {
        $this->post(route('registro.guardar'), [
            'name' => 'Lucia Rios', 'email' => 'lucia@est.univalle.edu',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertSessionHasNoErrors()->assertSessionHas('exito');

        $enviados = $this->correosDe('emails.bienvenida');
        $this->assertCount(1, $enviados);
        $this->assertTrue($enviados->first()->hasTo('lucia@est.univalle.edu'));
        $this->assertDatabaseHas('correos', ['para' => 'lucia@est.univalle.edu', 'tipo' => 'bienvenida']);
    }

    public function test_enlace_de_bienvenida_confirma_el_correo(): void
    {
        $cliente = $this->usuario();
        $this->assertNull($cliente->email_verified_at);

        $this->get(Notificador::enlaceVerificacion($cliente))->assertRedirect(route('login'));
        $this->assertNotNull($cliente->fresh()->email_verified_at);

        // Enlace manipulado (sin firma válida) no sirve
        $otro = $this->usuario();
        $this->get(route('correo.verificar', ['id' => $otro->id, 'hash' => sha1($otro->email)]))->assertStatus(403);
        $this->assertNull($otro->fresh()->email_verified_at);
    }

    public function test_cliente_puede_reenviar_confirmacion_desde_perfil(): void
    {
        $cliente = $this->usuario();
        $this->actingAs($cliente)->get(route('perfil.show'))->assertOk()->assertSee('Sin confirmar');
        $this->actingAs($cliente)->post(route('perfil.verificacion'))->assertSessionHas('exito');
        $this->assertCount(1, $this->correosDe('emails.bienvenida'));
    }

    public function test_recuperar_contrasena_envia_correo_cafeqr(): void
    {
        $cliente = $this->usuario('cliente', 'olvido@est.univalle.edu');
        $this->post(route('password.email'), ['email' => 'OLVIDO@est.univalle.edu'])->assertSessionHas('exito');

        $enviados = $this->correosDe('emails.recuperar');
        $this->assertCount(1, $enviados);
        $this->assertStringContainsString('/restablecer/', $enviados->first()->datos['url']);

        // Correo que no existe: misma respuesta y no se envía nada
        $this->post(route('password.email'), ['email' => 'nadie@est.univalle.edu'])->assertSessionHas('exito');
        $this->assertCount(1, $this->correosDe('emails.recuperar'));
    }

    public function test_si_gmail_falla_no_se_rompe_y_queda_registrado(): void
    {
        // Servidor de correo inexistente: el envío falla de verdad
        Mail::swap(new \Illuminate\Mail\MailManager($this->app));
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);
        app('mail.manager')->purge();

        // Registro: la cuenta se crea igual y avisa del problema
        $this->post(route('registro.guardar'), [
            'name' => 'Pedro Paz', 'email' => 'pedro@est.univalle.edu',
            'password' => 'Clave12345', 'password_confirmation' => 'Clave12345',
        ])->assertRedirect(route('dashboard'))->assertSessionHas('error');
        $this->assertAuthenticated();

        $fallido = Correo::where('para', 'pedro@est.univalle.edu')->firstOrFail();
        $this->assertSame('fallido', $fallido->estado);
        $this->assertSame(2, $fallido->intentos);   // se reintentó
        $this->assertNotEmpty($fallido->html);

        // Recuperar contraseña: tampoco da error 500
        auth()->logout();
        $this->post(route('password.email'), ['email' => 'pedro@est.univalle.edu'])->assertRedirect()->assertSessionHas('error');

        // El admin lo ve y lo puede reenviar (vuelve a fallar sin romperse)
        $this->actingAs($this->admin())->get(route('admin.correos', ['estado' => 'fallido']))->assertOk()->assertSee('pedro@est.univalle.edu');
        $this->actingAs($this->admin())->post(route('admin.correos.reenviar', $fallido))->assertSessionHas('error');
    }

    public function test_admin_ve_correos_y_reenvia(): void
    {
        $cliente = $this->usuario();
        Notificador::bienvenida($cliente);
        $correo = Correo::latest('id')->firstOrFail();
        $correo->update(['estado' => 'fallido', 'error' => 'prueba']);

        $this->actingAs($this->admin())->get(route('admin.panel'))->assertOk()->assertSee('Correos que no se enviaron');
        $this->actingAs($this->admin())->get(route('admin.correos'))->assertOk()->assertSee($cliente->email);
        $this->actingAs($this->admin())->post(route('admin.correos.reenviar', $correo))->assertSessionHas('exito');
        $this->assertNotSame('fallido', $correo->fresh()->estado);

        $this->actingAs($cliente)->get(route('admin.correos'))->assertRedirect();
    }

    public function test_admin_nuevo_admi7_y_reemplaza_al_viejo(): void
    {
        $this->assertSame('admi7@est.univalle.edu', config('cafeqr.admin.email'));
        $this->post(route('login.entrar'), ['email' => 'admi7@est.univalle.edu', 'password' => 'Admi1234@'])->assertRedirect(route('admin.panel'));
        auth()->logout();

        // Si existía un admin con el correo viejo, se actualiza ese mismo (no queda duplicado)
        $this->admin()->update(['email' => 'viejo@est.univalle.edu', 'password' => 'Vieja12345']);
        (new \Database\Seeders\AdminSeeder)->run();
        $this->assertSame(1, User::where('rol', 'administrador')->count());
        $this->assertSame('admi7@est.univalle.edu', $this->admin()->email);
        $this->assertDatabaseMissing('users', ['email' => 'viejo@est.univalle.edu']);
        $this->post(route('login.entrar'), ['email' => 'admi7@est.univalle.edu', 'password' => 'Admi1234@']);
        $this->assertAuthenticatedAs($this->admin());
    }
}
