<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'user_id',
        'estado_pedido_id',
        'numero_pedido',
        'codigo_recojo',
        'total',
        'listo',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'listo' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'estado_pedido_id');
    }

    public function detalle()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function pago()
    {
        return $this->hasOne(Pago::class);
    }

    /** Genera el siguiente número de pedido correlativo (#1000, #1001, ...). */
    public static function siguienteNumero(): int
    {
        $ultimo = static::max('numero_pedido');

        return $ultimo ? $ultimo + 1 : 1000;
    }
}
