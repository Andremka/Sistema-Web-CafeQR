<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['categoria_id', 'nombre', 'precio', 'icono', 'estado'];

    protected function casts(): array
    {
        return ['precio' => 'decimal:2'];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
}
