<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Siembra el catálogo de CafeQR: 4 categorías y 34 productos,
 * equivalente al menú validado en el prototipo de frontend.
 */
class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'bebidas' => ['nombre' => 'Bebidas', 'icono' => '☕', 'orden' => 1],
            'salados' => ['nombre' => 'Salados', 'icono' => '🥪', 'orden' => 2],
            'dulces' => ['nombre' => 'Dulces', 'icono' => '🧁', 'orden' => 3],
            'combos' => ['nombre' => 'Combos', 'icono' => '🍽️', 'orden' => 4],
        ];

        $ids = [];
        foreach ($categorias as $clave => $datos) {
            $ids[$clave] = Categoria::firstOrCreate(['nombre' => $datos['nombre']], $datos)->id;
        }

        $productos = [
            'bebidas' => [
                ['Café Americano', 6, '☕'], ['Cappuccino', 9, '☕'], ['Mocaccino', 10, '☕'],
                ['Chocolate Caliente', 7, '🍫'], ['Té Emoliente', 4, '🍵'], ['Té Verde', 4, '🍵'],
                ['Frappé de Café', 12, '🥤'], ['Milkshake de Vainilla', 12, '🥤'],
                ['Jugo Natural', 8, '🧃'], ['Jugo de Maracuyá', 8, '🧃'], ['Limonada', 6, '🍋'],
            ],
            'salados' => [
                ['Sandwich Mixto', 12, '🥪'], ['Salteña', 7, '🥟'], ['Empanada de Queso', 6, '🫓'],
                ['Empanada de Carne', 7, '🥟'], ['Tostada Jamón y Queso', 9, '🍞'],
                ['Choripán', 10, '🌭'], ['Wrap de Pollo', 11, '🌯'], ['Hamburguesa Sencilla', 15, '🍔'],
            ],
            'dulces' => [
                ['Muffin', 5, '🧁'], ['Brownie', 6, '🍫'], ['Galletas de Avena', 4, '🍪'],
                ['Alfajor', 5, '🥮'], ['Croissant', 6, '🥐'], ['Flan', 6, '🍮'],
                ['Panqueque', 8, '🥞'], ['Waffle con Miel', 9, '🧇'],
                ['Cheesecake', 8, '🍰'], ['Torta de Chocolate (porción)', 9, '🍰'],
            ],
            'combos' => [
                ['Combo Tarde (Té + Alfajor)', 8, '🍽️'],
                ['Combo Desayuno (Café + Muffin)', 10, '🍽️'],
                ['Combo Salado (Sandwich + Jugo)', 18, '🍽️'],
                ['Combo Energía (Frappé + Brownie)', 16, '🍽️'],
                ['Combo Ejecutivo (Sandwich + Jugo + Muffin)', 22, '🍽️'],
            ],
        ];

        foreach ($productos as $clave => $items) {
            foreach ($items as [$nombre, $precio, $icono]) {
                Producto::firstOrCreate(
                    ['nombre' => $nombre],
                    ['categoria_id' => $ids[$clave], 'precio' => $precio, 'icono' => $icono, 'estado' => 'disponible']
                );
            }
        }
    }
}
