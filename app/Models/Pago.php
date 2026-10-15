<?php

namespace App\Models;

use App\Services\Notificador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Pago extends Model
{
    public const METODO_QR = 'qr';
    public const METODO_EFECTIVO = 'efectivo';

    /** Pagos que todavía no terminaron. */
    public const ABIERTOS = ['pendiente', 'por_verificar', 'recibido', 'por_cobrar'];

    protected $fillable = ['pedido_id', 'metodo', 'referencia', 'monto', 'estado', 'vence_en'];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'vence_en' => 'datetime'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function verificaciones(): HasMany
    {
        return $this->hasMany(Verificacion::class);
    }

    public function esEfectivo(): bool
    {
        return $this->metodo === self::METODO_EFECTIVO;
    }

    /** Crea un pago nuevo y cierra los anteriores que seguían abiertos. */
    public static function iniciarPara(Pedido $pedido, string $metodo): self
    {
        $pedido->pagos()->whereIn('estado', self::ABIERTOS)->update(['estado' => 'cancelado']);

        $efectivo = $metodo === self::METODO_EFECTIVO;

        return self::create([
            'pedido_id' => $pedido->id,
            'metodo' => $metodo,
            'referencia' => Str::upper(Str::random(24)),
            'monto' => $pedido->total,
            'estado' => $efectivo ? 'por_cobrar' : 'pendiente',
            'vence_en' => $efectivo ? null : now()->addMinutes(max(1, (int) config('cafeqr.pago_minutos'))),
        ]);
    }

    public function estaVencido(): bool
    {
        return $this->estado === 'pendiente' && $this->vence_en && $this->vence_en->isPast();
    }

    /** Si el QR venció sin pagarse, lo marca como fallido. */
    public function refrescarVencimiento(): void
    {
        if ($this->estaVencido()) {
            $this->update(['estado' => 'fallido']);
        }
    }

    public function marcarPorVerificar(): void
    {
        if ($this->estado === 'pendiente') {
            $this->update(['estado' => 'por_verificar']);
        }
    }

    /** Se recibió el dinero: se envía el código al correo. Devuelve true si el correo salió. */
    public function marcarRecibido(): bool
    {
        $this->update(['estado' => 'recibido']);

        return Verificacion::enviarA($this->load('pedido.user'));
    }

    public function rechazar(): void
    {
        if (in_array($this->estado, self::ABIERTOS, true)) {
            $this->update(['estado' => 'fallido']);
        }
    }

    /** Termina el pago: el pedido queda pagado, con código de recojo y factura. */
    public function completar(): bool
    {
        $pedido = $this->pedido;

        if ($pedido->estado !== Pedido::PENDIENTE_PAGO) {
            return false;
        }

        DB::transaction(function () use ($pedido) {
            $this->update(['estado' => 'completado']);
            $pedido->update([
                'estado' => Pedido::PAGADO,
                'pagado_en' => now(),
                'metodo_pago' => $this->metodo,
                'codigo' => $pedido->codigo ?: Pedido::generarCodigoUnico(),
            ]);
            Factura::emitir($pedido);
        });

        Notificador::pagoConfirmado($pedido->fresh(['user', 'factura', 'detalles']));

        return true;
    }
}
