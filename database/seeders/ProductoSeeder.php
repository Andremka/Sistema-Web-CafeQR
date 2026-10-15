<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Menú de la cafetería. Se puede ejecutar varias veces sin riesgo:
 *   php artisan db:seed --class=ProductoSeeder
 * Solo agrega los productos que faltan (por nombre); no cambia precios, descripciones ni
 * disponibilidad de los que ya existen.
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            ['Espresso', 'Café corto e intenso, una sola medida', 5, 'Bebidas calientes'],
            ['Café americano', 'Café negro recién preparado', 5, 'Bebidas calientes'],
            ['Café con leche', 'Espresso con leche vaporizada', 7, 'Bebidas calientes'],
            ['Capuchino', 'Espresso, leche y espuma cremosa', 9, 'Bebidas calientes'],
            ['Latte vainilla', 'Espresso suave con leche y vainilla', 11, 'Bebidas calientes'],
            ['Moka', 'Espresso con chocolate y leche', 12, 'Bebidas calientes'],
            ['Chocolate caliente', 'Chocolate espeso con leche', 9, 'Bebidas calientes'],
            ['Té con canela', 'Infusión caliente de té y canela', 5, 'Bebidas calientes'],
            ['Té de manzanilla', 'Infusión relajante de manzanilla', 5, 'Bebidas calientes'],
            ['Mate de coca', 'Infusión tradicional de hoja de coca', 4, 'Bebidas calientes'],
            ['Api con pastel', 'Api morado caliente con pastel frito', 10, 'Bebidas calientes'],
            ['Limonada', 'Limonada natural con hielo', 8, 'Bebidas frías'],
            ['Jugo de naranja', 'Jugo natural exprimido', 10, 'Bebidas frías'],
            ['Jugo de papaya', 'Papaya fresca licuada, sin azúcar añadida', 10, 'Bebidas frías'],
            ['Mocochinchi', 'Refresco tradicional de durazno deshidratado', 6, 'Bebidas frías'],
            ['Frappé de café', 'Café frío batido con hielo y crema', 13, 'Bebidas frías'],
            ['Smoothie de frutilla', 'Frutilla, yogur y hielo', 13, 'Bebidas frías'],
            ['Batido de plátano', 'Plátano y leche licuados', 11, 'Bebidas frías'],
            ['Té helado', 'Té negro frío con limón', 7, 'Bebidas frías'],
            ['Agua mineral 500 ml', 'Agua con o sin gas', 5, 'Bebidas frías'],
            ['Refresco 500 ml', 'Gaseosa personal', 6, 'Bebidas frías'],
            ['Sándwich de pollo', 'Pollo, lechuga y tomate en pan de molde', 15, 'Sándwiches'],
            ['Sándwich de jamón y queso', 'Clásico, servido caliente', 13, 'Sándwiches'],
            ['Tostado de queso', 'Pan tostado con queso fundido', 10, 'Sándwiches'],
            ['Sándwich de atún', 'Atún, cebolla y mayonesa ligera', 15, 'Sándwiches'],
            ['Sándwich vegetariano', 'Palta, tomate, lechuga y queso', 14, 'Sándwiches'],
            ['Croissant de jamón y queso', 'Croissant horneado hoy', 14, 'Sándwiches'],
            ['Almuerzo del día', 'Sopa y segundo del día con refresco', 20, 'Comidas'],
            ['Hamburguesa clásica', 'Carne, queso, lechuga y tomate', 18, 'Comidas'],
            ['Hot dog', 'Salchicha, pan suave y salsas', 12, 'Comidas'],
            ['Salchipapa', 'Papas fritas con salchicha', 14, 'Comidas'],
            ['Pollo con papas', 'Presa de pollo frito con papas y ensalada', 22, 'Comidas'],
            ['Silpancho', 'Carne apanada, arroz, papa, huevo y ensalada', 25, 'Comidas'],
            ['Pizza personal', 'Pizza de queso con jamón', 18, 'Comidas'],
            ['Pasta boloñesa', 'Pasta con salsa de carne', 20, 'Comidas'],
            ['Ensalada fresca', 'Lechuga, tomate, zanahoria y pollo', 16, 'Comidas'],
            ['Porción de torta', 'Torta del día', 10, 'Postres'],
            ['Brownie', 'Brownie de chocolate', 8, 'Postres'],
            ['Cheesecake', 'Porción con salsa de frutos rojos', 12, 'Postres'],
            ['Muffin de arándanos', 'Esponjoso, horneado hoy', 7, 'Postres'],
            ['Flan de leche', 'Flan casero con caramelo', 7, 'Postres'],
            ['Gelatina con crema', 'Gelatina de sabores con crema', 5, 'Postres'],
            ['Helado de crema', 'Dos bolas a elección', 9, 'Postres'],
            ['Alfajor', 'Alfajor relleno de dulce de leche', 5, 'Postres'],
            ['Salteña de carne', 'Salteña jugosa, horneada hoy', 8, 'Snacks'],
            ['Salteña de pollo', 'Salteña de pollo con ají suave', 8, 'Snacks'],
            ['Empanada de queso', 'Empanada frita con queso', 7, 'Snacks'],
            ['Tucumana', 'Empanada frita rellena de carne y huevo', 9, 'Snacks'],
            ['Papas fritas', 'Porción crujiente con salsas', 9, 'Snacks'],
            ['Nachos con queso', 'Nachos con salsa de queso', 11, 'Snacks'],
            ['Dona glaseada', 'Dona con cobertura de chocolate', 6, 'Snacks'],
            ['Galletas', 'Paquete de galletas', 4, 'Snacks'],
            ['Mix de frutos secos', 'Almendras, maní y pasas', 8, 'Snacks'],
        ];

        foreach ($menu as [$nombre, $descripcion, $precio, $categoria]) {
            Producto::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'precio' => $precio, 'categoria' => $categoria, 'disponible' => true]
            );
        }
    }
}
