<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** categoría => ícono */
    public const CATEGORIAS = [
        'Bebidas calientes' => '☕',
        'Bebidas frías' => '🥤',
        'Sándwiches' => '🥪',
        'Comidas' => '🍔',
        'Postres' => '🍰',
        'Snacks' => '🍪',
        'Combos' => '🍽️',
    ];

    protected $fillable = ['nombre', 'descripcion', 'precio', 'categoria', 'disponible', 'imagen'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'disponible' => 'boolean',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function scopeDisponibles($consulta)
    {
        return $consulta->where('disponible', true);
    }

    /** Palabras del nombre => ícono. El primero que coincida gana; si ninguno, el de la categoría. */
    private const ICONOS = [
        'espresso' => '☕', 'café' => '☕', 'cafe' => '☕', 'capuchino' => '☕', 'latte' => '☕', 'moka' => '☕',
        'chocolate caliente' => '☕', 'té' => '🍵', 'té helado' => '🧋', 'mate' => '🍵', 'api con' => '🥣',
        'frapp' => '🧋', 'smoothie' => '🥤', 'batido' => '🥤', 'limonada' => '🍋', 'naranja' => '🍊',
        'papaya' => '🥭', 'durazno' => '🍑', 'mocochinchi' => '🍑', 'agua' => '💧', 'refresco' => '🥤', 'gaseosa' => '🥤',
        'hamburguesa' => '🍔', 'hot dog' => '🌭', 'pizza' => '🍕', 'pasta' => '🍝', 'ensalada' => '🥗',
        'salchipapa' => '🍟', 'papas' => '🍟', 'nuggets' => '🍗', 'pollo' => '🍗', 'silpancho' => '🥩',
        'almuerzo' => '🍽️', 'sopa' => '🍲',
        'croissant' => '🥐', 'tostado' => '🥪', 'sándwich' => '🥪', 'sandwich' => '🥪',
        'cheesecake' => '🍰', 'torta' => '🍰', 'brownie' => '🍫', 'muffin' => '🧁', 'flan' => '🍮',
        'gelatina' => '🍮', 'helado' => '🍨', 'alfajor' => '🍪', 'galleta' => '🍪', 'dona' => '🍩',
        'salteña' => '🥟', 'empanada' => '🥟', 'tucumana' => '🥟', 'nachos' => '🌮', 'chips' => '🥔',
        'frutos secos' => '🥜', 'fruta' => '🍎',
    ];

    public function getIconoAttribute(): string
    {
        $nombre = mb_strtolower($this->nombre ?? '');
        $iconos = self::ICONOS;
        // Las palabras más largas (más específicas) se revisan primero: "frappé de café" antes que "café".
        uksort($iconos, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        foreach ($iconos as $palabra => $icono) {
            if (str_contains($nombre, $palabra)) {
                return $icono;
            }
        }

        return self::CATEGORIAS[$this->categoria] ?? '🍽️';
    }

    /** Clase CSS de color según la categoría (ej. cat-bebidas-calientes). */
    public function getCategoriaClaseAttribute(): string
    {
        return 'cat-'.\Illuminate\Support\Str::slug($this->categoria ?? '');
    }

    public function getImagenUrlAttribute(): ?string
    {
        if ($this->imagen) {
            return asset('productos/'.$this->imagen);
        }

        // Foto suelta: public/productos/<nombre-del-producto>.jpg (jpg, jpeg, png o webp)
        $nombre = $this->nombre_archivo_foto;
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            if (is_file(public_path('productos'.DIRECTORY_SEPARATOR.$nombre.'.'.$ext))) {
                return asset('productos/'.$nombre.'.'.$ext);
            }
        }

        return null;
    }

    /** Nombre de archivo (sin extensión) que se busca como foto, ej. cafe-americano. */
    public function getNombreArchivoFotoAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->nombre ?? '');
    }
}
