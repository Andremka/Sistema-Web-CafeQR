<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Historial de correos enviados por CafeQR (enviado, fallido o en modo prueba). */
class Correo extends Model
{
    public const ENVIADO = 'enviado';
    public const FALLIDO = 'fallido';
    public const PRUEBA = 'prueba';

    public const TIPOS = [
        'bienvenida' => 'Bienvenida y verificación',
        'recuperar' => 'Recuperar contraseña',
        'codigo' => 'Código de pago',
        'pago-confirmado' => 'Pago confirmado',
        'en-preparacion' => 'En preparación',
        'listo' => 'Pedido listo',
        'cancelado' => 'Pedido cancelado',
        'prueba' => 'Prueba',
    ];

    protected $fillable = ['user_id', 'para', 'asunto', 'tipo', 'estado', 'error', 'intentos', 'html'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTipoTextoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }
}
