<?php

namespace App\Services;

use App\Models\CuentaCobro;
use App\Models\EstadoPedido;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagoService
{
    /**
     * Registra el pago de un carrito de sesión: crea el pedido, su detalle
     * y el pago asociado, genera el código de recojo y el número de pedido.
     *
     * @param  User   $usuario  Usuario autenticado que realiza la compra.
     * @param  array  $carrito  Carrito de sesión [producto_id => [nombre, precio, cantidad]].
     * @param  CuentaCobro  $cuenta  Cuenta bancaria que recibe el pago.
     * @return Pago   El pago recién creado, con su pedido cargado.
     */
    public function confirmar(User $usuario, array $carrito, CuentaCobro $cuenta): Pago
    {
        return DB::transaction(function () use ($usuario, $carrito, $cuenta) {
            $total = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);

            $pedido = Pedido::create([
                'user_id' => $usuario->id,
                'estado_pedido_id' => EstadoPedido::where('nombre', 'pagado')->value('id'),
                'numero_pedido' => Pedido::siguienteNumero(),
                'codigo_recojo' => random_int(1000, 9999),
                'total' => $total,
            ]);

            foreach ($carrito as $productoId => $item) {
                PedidoDetalle::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $productoId,
                    'nombre_producto' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                ]);
            }

            $pago = Pago::create([
                'pedido_id' => $pedido->id,
                'metodo_pago_id' => MetodoPago::where('nombre', 'QR')->value('id'),
                'monto' => $total,
                'codigo_transaccion' => strtoupper(Str::random(10)),
                'fecha_pago' => now(),
                'estado' => 'pagado',
                'banco_destino' => $cuenta->banco,
                'titular_destino' => $cuenta->titular,
                'numero_destino' => $cuenta->numero,
            ]);

            return $pago->load('pedido.detalle', 'pedido.usuario');
        });
    }
}
