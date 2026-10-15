<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Pedido extends Model
{
    public const CARRITO = 'carrito';
    public const PENDIENTE_PAGO = 'pendiente_pago';
    public const PAGADO = 'pagado';
    public const EN_PREPARACION = 'en_preparacion';
    public const LISTO = 'listo';
    public const ENTREGADO = 'entregado';
    public const CANCELADO = 'cancelado';

    /** Pedidos que la cafetería debe atender. */
    public const ACTIVOS = [self::PAGADO, self::EN_PREPARACION, self::LISTO];

    public const TEXTOS = [
        self::CARRITO => 'Armando pedido',
        self::PENDIENTE_PAGO => 'Pendiente de pago',
        self::PAGADO => 'Pagado',
        self::EN_PREPARACION => 'En preparación',
        self::LISTO => 'Listo para recoger',
        self::ENTREGADO => 'Entregado',
        self::CANCELADO => 'Cancelado',
    ];

    protected $fillable = [
        'user_id', 'estado', 'total', 'codigo', 'metodo_pago', 'razon_social', 'nit_ci',
        'tiempo_estimado', 'preparacion_inicio', 'pagado_en', 'listo_en', 'entregado_en',
        'cancelado_por', 'motivo_cancelacion', 'cancelado_en', 'reembolso_pendiente', 'reembolsado_en',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'tiempo_estimado' => 'integer',
            'preparacion_inicio' => 'datetime',
            'pagado_en' => 'datetime',
            'listo_en' => 'datetime',
            'entregado_en' => 'datetime',
            'cancelado_en' => 'datetime',
            'reembolsado_en' => 'datetime',
            'reembolso_pendiente' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class);
    }

    public function recalcularTotal(): void
    {
        $total = $this->detalles()->get()->sum(fn ($d) => (float) $d->precio_unitario * (int) $d->cantidad);
        $this->update(['total' => round($total, 2)]);
    }

    public function getEstadoTextoAttribute(): string
    {
        return self::TEXTOS[$this->estado] ?? ucfirst(str_replace('_', ' ', (string) $this->estado));
    }

    public function getCantidadItemsAttribute(): int
    {
        return (int) $this->detalles->sum('cantidad');
    }

    /** Momento en que se espera que esté listo (si ya está en preparación). */
    public function getListoEstimadoAttribute(): ?Carbon
    {
        if (! $this->preparacion_inicio || ! $this->tiempo_estimado) {
            return null;
        }

        return $this->preparacion_inicio->copy()->addMinutes($this->tiempo_estimado);
    }

    /** Minutos que faltan (0 si ya pasó). Null si todavía no hay tiempo estimado. */
    public function minutosRestantes(): ?int
    {
        $fin = $this->listo_estimado;
        if (! $fin || $this->estado !== self::EN_PREPARACION) {
            return null;
        }

        return max(0, (int) ceil(now()->diffInSeconds($fin, false) / 60));
    }

    public function pagoActivo(): ?Pago
    {
        return $this->pagos()->whereIn('estado', Pago::ABIERTOS)->latest('id')->first();
    }

    /** El cliente puede cancelar mientras no se haya empezado a preparar. */
    public function puedeCancelarCliente(): bool
    {
        return in_array($this->estado, [self::PENDIENTE_PAGO, self::PAGADO], true);
    }

    public function puedeCancelarPersonal(): bool
    {
        return in_array($this->estado, [self::PENDIENTE_PAGO, ...self::ACTIVOS], true);
    }

    /** $por: 'cliente' o 'personal'. Si ya había dinero recibido, queda un reembolso pendiente. */
    public function cancelar(string $por, ?string $motivo = null): void
    {
        $habiaDinero = $this->pagado_en !== null || $this->pagos()->where('estado', 'recibido')->exists();

        $this->pagos()->whereIn('estado', Pago::ABIERTOS)->update(['estado' => 'cancelado']);

        $this->update([
            'estado' => self::CANCELADO,
            'cancelado_por' => $por,
            'motivo_cancelacion' => $motivo ? Str::limit(trim($motivo), 250, '') : null,
            'cancelado_en' => now(),
            'reembolso_pendiente' => $habiaDinero,
        ]);
    }

    /** Código de recojo de 5 caracteres, sin letras que se confunden (O/0, I/1). */
    public static function generarCodigoUnico(): string
    {
        $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $codigo = '';
            for ($i = 0; $i < 5; $i++) {
                $codigo .= $letras[random_int(0, strlen($letras) - 1)];
            }
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
