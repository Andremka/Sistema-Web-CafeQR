<?php

namespace Database\Seeders;

use App\Models\EstadoPedido;
use Illuminate\Database\Seeder;

class EstadoPedidoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['pendiente', 'pagado', 'en_preparacion', 'listo', 'cancelado'] as $nombre) {
            EstadoPedido::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
