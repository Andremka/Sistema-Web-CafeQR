<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EstadoPedidoSeeder::class,
            MetodoPagoSeeder::class,
            CuentaCobroSeeder::class,
            CategoriaProductoSeeder::class,
            AdminUserSeeder::class,
            CuponSeeder::class,
        ]);
    }
}
