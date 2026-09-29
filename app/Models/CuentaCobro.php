<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cuenta bancaria que recibe los pagos de la cafetería.
 * Tabla singleton: se espera una única fila editable desde el módulo de pago.
 */
class CuentaCobro extends Model
{
    protected $table = 'cuenta_cobro';

    protected $fillable = ['banco', 'titular', 'numero'];
}
