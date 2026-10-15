<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    protected $fillable = ['pedido_id', 'numero', 'emitida_en'];

    protected function casts(): array
    {
        return ['emitida_en' => 'datetime'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /** Crea la factura del pedido (una sola vez) con número correlativo. */
    public static function emitir(Pedido $pedido): self
    {
        $existente = self::where('pedido_id', $pedido->id)->first();
        if ($existente) {
            return $existente;
        }

        return self::create([
            'pedido_id' => $pedido->id,
            'numero' => ((int) self::max('numero')) + 1,
            'emitida_en' => now(),
        ]);
    }

    public function getNumeroFormateadoAttribute(): string
    {
        return str_pad((string) $this->numero, 6, '0', STR_PAD_LEFT);
    }
}
