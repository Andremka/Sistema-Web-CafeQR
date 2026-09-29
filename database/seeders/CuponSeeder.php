<?php

namespace Database\Seeders;

use App\Models\Cupon;
use Illuminate\Database\Seeder;

class CuponSeeder extends Seeder
{
    public function run(): void
    {
        Cupon::firstOrCreate(['codigo' => 'UNIVALLE10'], ['monto' => 10]);
        Cupon::firstOrCreate(['codigo' => 'CAFEQR20'], ['monto' => 20]);
    }
}
