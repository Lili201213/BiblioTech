<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege rutas según el rol del usuario.
 * Uso en rutas: ->middleware('rol:administrador,bibliotecario')
 * Si el rol no coincide, responde 403 (prohibido).
 */
class Rol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        // Sin sesión o con un rol que no está en la lista → 403
        if (!$usuario || !in_array($usuario->rol, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
