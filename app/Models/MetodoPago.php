<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pago';

    protected $fillable = ['nombre', 'descripcion'];

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
