<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UsuarioRol extends Command
{
    protected $signature = 'usuario:rol {correo} {rol : cliente, empleado o administrador}';

    protected $description = 'Cambia el rol de un usuario desde la terminal';

    public function handle(): int
    {
        $rol = strtolower($this->argument('rol'));

        if (! array_key_exists($rol, User::ROLES)) {
            $this->error('Rol inválido. Usa: '.implode(', ', array_keys(User::ROLES)));

            return self::FAILURE;
        }

        $usuario = User::where('email', strtolower($this->argument('correo')))->first();

        if (! $usuario) {
            $this->error('No existe un usuario con ese correo.');

            return self::FAILURE;
        }

        $usuario->update(['rol' => $rol]);
        $this->info($usuario->name.' ahora es '.User::ROLES[$rol].'.');

        return self::SUCCESS;
    }
}
