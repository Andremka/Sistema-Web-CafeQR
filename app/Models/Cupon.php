<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    protected $fillable = ['codigo', 'monto'];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }
}
