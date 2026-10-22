<?php

namespace App\Providers;

use App\Models\Pedido;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paginación sencilla en español (sin depender de Tailwind)
        Paginator::defaultView('partials.paginacion');
        Paginator::defaultSimpleView('partials.paginacion');

        // Datos que necesita la barra superior en todas las pantallas del panel
        View::composer('layouts.app', function ($vista) {
            $usuario = Auth::user();
            $itemsCarrito = 0;
            $totalCarrito = 0.0;
            $pedidoListo = null;

            if ($usuario && $usuario->esCliente() && Schema::hasTable('pedidos')) {
                $enCurso = $usuario->pedidoEnCurso();
                $itemsCarrito = $enCurso ? (int) $enCurso->detalles()->sum('cantidad') : 0;
                $totalCarrito = $enCurso ? (float) $enCurso->detalles()->selectRaw('COALESCE(SUM(cantidad * precio_unitario), 0) as t')->value('t') : 0.0;
                $pedidoListo = $usuario->pedidos()->where('estado', Pedido::LISTO)->latest('id')->first();
            }

            $vista->with('itemsCarrito', $itemsCarrito)->with('totalCarrito', $totalCarrito)->with('pedidoListo', $pedidoListo);
        });
    }
}
