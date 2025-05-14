<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarPermiso
{
    public function handle(Request $request, Closure $next, $permiso)
    {
        if (!Auth::check() || !Auth::user()->tienePermiso($permiso)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
