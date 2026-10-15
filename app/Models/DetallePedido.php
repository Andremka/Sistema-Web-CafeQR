<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    protected $table = 'detalle_pedidos';

    protected $fillable = ['pedido_id', 'producto_id', 'nombre', 'precio_unitario', 'cantidad'];

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2', 'cantidad' => 'integer'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function getSubtotalAttribute(): float
    {
        return round((float) $this->precio_unitario * (int) $this->cantidad, 2);
    }
}
