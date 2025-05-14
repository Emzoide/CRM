<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PuedeGestionarUsuario
{
    /**
     * Manejar una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->route('usuario');
        
        // Si no se encontró el usuario, devolver 404
        if (!$usuario) {
            abort(404, 'Usuario no encontrado');
        }
        
        // Verificar si el usuario autenticado puede gestionar al usuario objetivo
        if (!Auth::user()->puedeGestionarUsuario($usuario)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para realizar esta acción.'
                ], 403);
            }
            
            return redirect()
                ->route('admin.usuarios.index')
                ->with('error', 'No tienes permiso para gestionar este usuario.');
        }
        
        return $next($request);
    }
}
