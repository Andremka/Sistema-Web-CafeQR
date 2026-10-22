<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea o actualiza al ADMINISTRADOR (único usuario creado por código).
 *   Correo:     admi7@est.univalle.edu
 *   Contraseña: Admi1234@
 * Se puede repetir sin riesgo:  php artisan db:seed --class=AdminSeeder
 * Si había un administrador con otro correo, se actualiza ese mismo (no queda duplicado).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $datos = config('cafeqr.admin');
        $correo = strtolower(trim($datos['email']));

        $admin = User::where('email', $correo)->first()
            ?? User::where('rol', User::ROL_ADMIN)->orderBy('id')->first()
            ?? new User;

        $admin->forceFill([
            'name' => $datos['nombre'],
            'email' => $correo,
            'rol' => User::ROL_ADMIN,
            'password' => $datos['password'],
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();
    }
}
