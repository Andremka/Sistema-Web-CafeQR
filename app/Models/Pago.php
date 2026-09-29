<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'pedido_id',
        'metodo_pago_id',
        'monto',
        'codigo_transaccion',
        'fecha_pago',
        'estado',
        'banco_destino',
        'titular_destino',
        'numero_destino',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
}
