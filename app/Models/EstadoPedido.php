<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
