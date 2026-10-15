<?php

namespace App\Models;

use App\Services\Notificador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Verificacion extends Model
{
    protected $table = 'verificaciones';

    public const MAX_INTENTOS = 5;
    public const MINUTOS = 10;

    protected $fillable = ['pago_id', 'user_id', 'codigo_hash', 'intentos', 'vence_en', 'usado'];

    protected function casts(): array
    {
        return ['vence_en' => 'datetime', 'usado' => 'boolean', 'intentos' => 'integer'];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    /** Genera un código de 6 dígitos y lo envía al correo del cliente. Devuelve true si el correo salió. */
    public static function enviarA(Pago $pago): bool
    {
        $usuario = $pago->pedido->user;
        $codigo = (string) random_int(100000, 999999);

        self::where('pago_id', $pago->id)->update(['usado' => true]);

        self::create([
            'pago_id' => $pago->id,
            'user_id' => $usuario->id,
            'codigo_hash' => Hash::make($codigo),
            'intentos' => 0,
            'vence_en' => now()->addMinutes(self::MINUTOS),
            'usado' => false,
        ]);

        return Notificador::codigo($usuario, $codigo, $pago);
    }

    /** Devuelve: ok | incorrecto | vencido | bloqueado | sin_codigo */
    public static function comprobar(Pago $pago, string $codigo): string
    {
        $v = self::where('pago_id', $pago->id)->where('usado', false)->latest('id')->first();

        if (! $v) {
            return 'sin_codigo';
        }
        if ($v->vence_en->isPast()) {
            return 'vencido';
        }
        if ($v->intentos >= self::MAX_INTENTOS) {
            return 'bloqueado';
        }

        if (! Hash::check($codigo, $v->codigo_hash)) {
            $v->increment('intentos');

            return $v->intentos >= self::MAX_INTENTOS ? 'bloqueado' : 'incorrecto';
        }

        $v->update(['usado' => true]);

        return 'ok';
    }

    public static function intentosRestantes(Pago $pago): int
    {
        $v = self::where('pago_id', $pago->id)->where('usado', false)->latest('id')->first();

        return $v ? max(0, self::MAX_INTENTOS - $v->intentos) : 0;
    }
}
