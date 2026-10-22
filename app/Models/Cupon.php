<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Cupón de descuento: resta un monto fijo en bolivianos al pedido. */
class Cupon extends Model
{
    protected $table = 'cupones';

    protected $fillable = ['codigo', 'monto', 'activo'];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'activo' => 'boolean'];
    }

    public static function buscar(string $codigo): ?self
    {
        $codigo = strtoupper(preg_replace('/\s+/', '', $codigo));

        return $codigo === '' ? null : self::where('codigo', $codigo)->where('activo', true)->first();
    }
}
