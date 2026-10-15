<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea (o actualiza) al ADMINISTRADOR. Es la única cuenta que se crea "a mano en el código";
 * los clientes se registran solos y los empleados los crea el administrador desde su panel.
 * Datos configurables en .env: ADMIN_NOMBRE, ADMIN_EMAIL, ADMIN_PASSWORD
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('cafeqr.admin');

        User::updateOrCreate(
            ['email' => strtolower($admin['email'])],
            [
                'name' => $admin['nombre'],
                'rol' => User::ROL_ADMIN,
                'password' => $admin['password'],
            ]
        );
    }
}
