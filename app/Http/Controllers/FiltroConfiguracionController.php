<?php

namespace App\Http\Controllers;

use App\Models\FiltroConfiguracion;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FiltroConfiguracionController extends Controller
{
    /**
     * Obtiene los filtros disponibles para el usuario actual
     */
    public function index()
    {
        $usuario = Auth::user();
        $filtros = FiltroConfiguracion::disponiblesParaUsuario($usuario)
            ->orderBy('rol_id')
            ->orderBy('orden')
            ->get();
            
        return response()->json($filtros);
    }
    
    /**
     * Guarda un nuevo filtro
     */
    public function store(Request $request)
    {
        try {
            $usuario = Auth::user();
            
            // Validación básica
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'rol_id' => 'nullable|exists:roles,id',
                'es_predeterminado' => 'boolean',
                'configuracion' => 'required|array',
                'orden' => 'integer',
            ]);
            
            // Verificar permisos para crear filtros a nivel de rol
            if (!empty($data['rol_id']) && $data['usuario_id'] === null) {
                // Solo administradores pueden crear filtros para roles
                if (!$usuario->tienePermiso('gestionar_filtros_globales')) {
                    return response()->json([
                        'error' => 'No tienes permiso para crear filtros a nivel de rol'
                    ], 403);
                }
            }
            
            // Añadir el usuario actual como creador si es un filtro personal
            if (empty($data['rol_id'])) {
                $data['usuario_id'] = $usuario->id;
            }
            
            // Si el filtro es predeterminado, quitar ese estado de los demás filtros del mismo nivel
            if ($data['es_predeterminado']) {
                if (empty($data['rol_id'])) {
                    // Filtro personal
                    FiltroConfiguracion::where('usuario_id', $usuario->id)
                        ->where('es_predeterminado', true)
                        ->update(['es_predeterminado' => false]);
                } else {
                    // Filtro de rol
                    FiltroConfiguracion::where('rol_id', $data['rol_id'])
                        ->whereNull('usuario_id')
                        ->where('es_predeterminado', true)
                        ->update(['es_predeterminado' => false]);
                }
            }
            
            $filtro = FiltroConfiguracion::create($data);
            
            return response()->json($filtro, 201);
        } catch (\Exception $e) {
            Log::error('Error al crear filtro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al crear el filtro'
            ], 500);
        }
    }
    
    /**
     * Actualiza un filtro existente
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = Auth::user();
            $filtro = FiltroConfiguracion::findOrFail($id);
            
            // Verificar permisos
            if ($filtro->usuario_id && $filtro->usuario_id !== $usuario->id) {
                return response()->json([
                    'error' => 'No tienes permiso para modificar este filtro'
                ], 403);
            }
            
            if ($filtro->usuario_id === null && !$usuario->tienePermiso('gestionar_filtros_globales')) {
                return response()->json([
                    'error' => 'No tienes permiso para modificar filtros globales'
                ], 403);
            }
            
            // Validación básica
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'es_predeterminado' => 'boolean',
                'configuracion' => 'required|array',
                'orden' => 'integer',
            ]);
            
            // Si el filtro se marca como predeterminado, quitar ese estado de los demás filtros del mismo nivel
            if ($data['es_predeterminado'] && !$filtro->es_predeterminado) {
                if ($filtro->usuario_id) {
                    // Filtro personal
                    FiltroConfiguracion::where('usuario_id', $filtro->usuario_id)
                        ->where('id', '!=', $filtro->id)
                        ->where('es_predeterminado', true)
                        ->update(['es_predeterminado' => false]);
                } else {
                    // Filtro de rol
                    FiltroConfiguracion::where('rol_id', $filtro->rol_id)
                        ->whereNull('usuario_id')
                        ->where('id', '!=', $filtro->id)
                        ->where('es_predeterminado', true)
                        ->update(['es_predeterminado' => false]);
                }
            }
            
            // Actualizar el filtro
            $filtro->update($data);
            
            return response()->json($filtro);
        } catch (\Exception $e) {
            Log::error('Error al actualizar filtro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar el filtro'
            ], 500);
        }
    }
    
    /**
     * Elimina un filtro
     */
    public function destroy($id)
    {
        try {
            $usuario = Auth::user();
            $filtro = FiltroConfiguracion::findOrFail($id);
            
            // Verificar permisos
            if ($filtro->usuario_id && $filtro->usuario_id !== $usuario->id) {
                return response()->json([
                    'error' => 'No tienes permiso para eliminar este filtro'
                ], 403);
            }
            
            if ($filtro->usuario_id === null && !$usuario->tienePermiso('gestionar_filtros_globales')) {
                return response()->json([
                    'error' => 'No tienes permiso para eliminar filtros globales'
                ], 403);
            }
            
            // Eliminar el filtro
            $filtro->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar filtro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al eliminar el filtro'
            ], 500);
        }
    }
    
    /**
     * Obtener un filtro específico
     */
    public function show($id)
    {
        try {
            $usuario = Auth::user();
            $filtro = FiltroConfiguracion::findOrFail($id);
            
            // Verificar permisos
            if ($filtro->usuario_id && $filtro->usuario_id !== $usuario->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver este filtro'
                ], 403);
            }
            
            if ($filtro->rol_id) {
                // Verificar que el usuario tenga acceso a este filtro de rol
                $tieneRol = false;
                foreach ($usuario->roles as $rol) {
                    if ($rol->id === $filtro->rol_id) {
                        $tieneRol = true;
                        break;
                    }
                }
                
                if (!$tieneRol && !$usuario->tienePermiso('gestionar_filtros_globales')) {
                    return response()->json([
                        'error' => 'No tienes permiso para ver este filtro'
                    ], 403);
                }
            }
            
            return response()->json($filtro);
        } catch (\Exception $e) {
            Log::error('Error al obtener filtro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener el filtro'
            ], 500);
        }
    }
}
