<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROL_CLIENTE = 'cliente';
    public const ROL_EMPLEADO = 'empleado';
    public const ROL_ADMIN = 'administrador';

    public const ROLES = [
        self::ROL_CLIENTE => 'Cliente',
        self::ROL_EMPLEADO => 'Empleado',
        self::ROL_ADMIN => 'Administrador',
    ];

    protected $fillable = ['name', 'email', 'telefono', 'foto', 'rol', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /** Pedido que el cliente está armando o que espera pago. */
    public function pedidoEnCurso(): ?Pedido
    {
        return $this->pedidos()
            ->whereIn('estado', [Pedido::CARRITO, Pedido::PENDIENTE_PAGO])
            ->latest('id')
            ->first();
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esEmpleado(): bool
    {
        return $this->rol === self::ROL_EMPLEADO;
    }

    public function esCliente(): bool
    {
        return $this->rol === self::ROL_CLIENTE;
    }

    /** Empleado o administrador. */
    public function esPersonal(): bool
    {
        return $this->esEmpleado() || $this->esAdministrador();
    }

    /** Nombre de la ruta a la que entra cada rol al iniciar sesión. */
    public function rutaInicio(): string
    {
        return match ($this->rol) {
            self::ROL_ADMIN => 'admin.panel',
            self::ROL_EMPLEADO => 'entregas.index',
            default => 'dashboard',
        };
    }

    public function getRolTextoAttribute(): string
    {
        return self::ROLES[$this->rol] ?? ucfirst((string) $this->rol);
    }

    public function getInicialAttribute(): string
    {
        return mb_strtoupper(mb_substr(trim((string) $this->name), 0, 1)) ?: '?';
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('fotos/'.$this->foto) : null;
    }
}
