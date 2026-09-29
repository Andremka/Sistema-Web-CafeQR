<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        MetodoPago::firstOrCreate(['nombre' => 'QR'], ['descripcion' => 'Pago mediante código QR (HU-10)']);
    }
}
