<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso al Panel de administración a la cuenta con rol
 * "administrador" (ver AdminUserSeeder — admin@cafeqr.edu).
 */
class EsAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->esAdministrador()) {
            abort(403, 'Esa sección es solo para el administrador.');
        }

        return $next($request);
    }
}
