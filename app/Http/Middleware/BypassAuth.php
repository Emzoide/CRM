<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassAuth
{
    /**
     * Middleware temporal para bypasear todas las verificaciones de autenticación
     */
    public function handle(Request $request, Closure $next)
    {
        // Simplemente permitir todas las solicitudes
        return $next($request);
    }
}
