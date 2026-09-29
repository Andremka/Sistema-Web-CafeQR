<?php

namespace Database\Seeders;

use App\Models\CuentaCobro;
use Illuminate\Database\Seeder;

class CuentaCobroSeeder extends Seeder
{
    public function run(): void
    {
        CuentaCobro::firstOrCreate([], [
            'banco' => 'Banco Nacional de Bolivia',
            'titular' => 'Cafetería CafeQR · UNIVALLE',
            'numero' => '4013256789',
        ]);
    }
}
