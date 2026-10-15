<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso en rutas:  ->middleware('rol:empleado,administrador')
 */
class Rol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! in_array($usuario->rol, $roles, true)) {
            return redirect()->route($usuario->rutaInicio())
                ->with('error', 'No tienes permiso para entrar a esa sección.');
        }

        return $next($request);
    }
}
