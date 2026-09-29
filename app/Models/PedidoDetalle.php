<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $table = 'pedido_detalle';

    protected $fillable = ['pedido_id', 'producto_id', 'nombre_producto', 'cantidad', 'precio_unitario'];

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2'];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function subtotal(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
