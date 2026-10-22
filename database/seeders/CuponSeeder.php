<?php

namespace Database\Seeders;

use App\Models\Cupon;
use Illuminate\Database\Seeder;

/** Cupones del equipo (vienen de la rama main). Se puede repetir sin riesgo. */
class CuponSeeder extends Seeder
{
    public function run(): void
    {
        Cupon::firstOrCreate(['codigo' => 'UNIVALLE10'], ['monto' => 10]);
        Cupon::firstOrCreate(['codigo' => 'CAFEQR20'], ['monto' => 20]);
    }
}
