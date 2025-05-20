<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Tienda;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    /**
     * Devuelve los datos de un rol en formato JSON para edición AJAX.
     */
    public function show($id)
    {
        $rol = Rol::with(['permisos', 'tiendasGestionables', 'rolesGestionables'])->find($id);
        if (!$rol) {
            return response()->json([
                'error' => 'Rol no encontrado',
                'id' => $id
            ], 404);
        }
        return response()->json([
            'id' => $rol->id,
            'nombre' => $rol->nombre,
            'descripcion' => $rol->descripcion,
            'is_admin' => $rol->is_admin,
            'permisos' => $rol->permisos->pluck('id'),
            'tiendas_gestionables' => $rol->tiendas_gestionables ?? [],
            'roles_gestionables' => $rol->roles_gestionables ?? [],
            'tiendas_rel' => $rol->tiendasGestionables->pluck('id'),
            'roles_rel' => $rol->rolesGestionables->pluck('id'),
        ]);
    }
    public function index()
    {
        $roles = Rol::with(['permisos', 'tiendasGestionables', 'rolesGestionables'])->get();
        $permisos = Permiso::orderBy('grupo')->get();
        $tiendas = Tienda::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();
        
        return View::make('admin.roles.index', compact('roles', 'permisos', 'tiendas', 'sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string',
            'is_admin' => 'boolean',
            'permisos' => 'array',
            'tiendas_gestionables' => 'array',
            'roles_gestionables' => 'array'
        ]);

        try {
            $rol = Rol::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'is_admin' => $request->is_admin ?? false,
                'tiendas_gestionables' => $request->tiendas_gestionables ?? null,
                'roles_gestionables' => $request->roles_gestionables ?? null
            ]);

            if (!$rol->is_admin && $request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            }
            
            // Sincronizar las relaciones many-to-many
            if ($request->has('tiendas_rel')) {
                $rol->tiendasGestionables()->sync($request->tiendas_rel);
            }
            
            if ($request->has('roles_rel')) {
                // Verificar que no haya ciclos (un rol no puede gestionarse a sí mismo)
                $rolesRelacionados = array_filter($request->roles_rel, function($rolId) use ($rol) {
                    return $rolId != $rol->id; // Evitar que un rol se gestione a sí mismo
                });
                
                $rol->rolesGestionables()->sync($rolesRelacionados);
            }

            Log::info("Rol creado:", [
                'id' => $rol->id,
                'nombre' => $rol->nombre,
                'is_admin' => $rol->is_admin,
                'permisos' => $rol->permisos->pluck('nombre'),
                'tiendas_gestionables' => $rol->tiendas_gestionables,
                'roles_gestionables' => $rol->roles_gestionables
            ]);

            return Redirect::route('admin.roles.index')
                ->with('success', 'Rol creado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al crear rol: " . $e->getMessage());
            Log::error("Datos enviados: ", $request->all());
            return Redirect::back()
                ->with('error', 'Error al crear el rol. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    public function update(Request $request, Rol $rol)
    {
        // Asegurar que tenemos el rol correcto antes de validar
        $rolId = $request->input('rol_id') ?? $rol->id;
        
        // Si se pasó un ID en rol_id y es diferente al del modelo inyectado
        if ($rolId && $rolId != $rol->id) {
            $rol = Rol::findOrFail($rolId);
        }
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $rol->id,
            'descripcion' => 'nullable|string',
            'is_admin' => 'boolean',
            'permisos' => 'array',
            'tiendas_gestionables' => 'array',
            'roles_gestionables' => 'array',
            'tiendas_rel' => 'array',
            'roles_rel' => 'array'
        ]);

        try {
            $rol->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'is_admin' => $request->is_admin ?? false,
                'tiendas_gestionables' => $request->tiendas_gestionables ?? null,
                'roles_gestionables' => $request->roles_gestionables ?? null
            ]);

            if (!$rol->is_admin && $request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            } else {
                $rol->permisos()->detach();
            }
            
            // Sincronizar las relaciones many-to-many
            if ($request->has('tiendas_rel')) {
                $rol->tiendasGestionables()->sync($request->tiendas_rel);
            } else {
                $rol->tiendasGestionables()->detach();
            }
            
            if ($request->has('roles_rel')) {
                // Verificar que no haya ciclos (un rol no puede gestionarse a sí mismo)
                $rolesRelacionados = array_filter($request->roles_rel, function($rolId) use ($rol) {
                    return $rolId != $rol->id; // Evitar que un rol se gestione a sí mismo
                });
                
                $rol->rolesGestionables()->sync($rolesRelacionados);
            } else {
                $rol->rolesGestionables()->detach();
            }

            Log::info("Rol actualizado:", [
                'id' => $rol->id,
                'nombre' => $rol->nombre,
                'is_admin' => $rol->is_admin,
                'permisos' => $rol->permisos->pluck('nombre'),
                'tiendas_gestionables' => $rol->tiendas_gestionables,
                'roles_gestionables' => $rol->roles_gestionables
            ]);

            return Redirect::route('admin.roles.index')
                ->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar rol: " . $e->getMessage());
            Log::error("Datos enviados: ", $request->all());
            return Redirect::back()
                ->with('error', 'Error al actualizar el rol. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    public function destroy(Rol $rol)
    {
        if ($rol->is_admin) {
            return Redirect::back()
                ->with('error', 'No se puede eliminar el rol de administrador.');
        }

        if ($rol->usuarios()->exists()) {
            return Redirect::back()
                ->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        try {
            $rol->permisos()->detach();
            $rol->delete();

            Log::info("Rol eliminado:", [
                'id' => $rol->id,
                'nombre' => $rol->nombre
            ]);

            return Redirect::route('admin.roles.index')
                ->with('success', 'Rol eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar rol: " . $e->getMessage());
            return Redirect::back()
                ->with('error', 'Error al eliminar el rol. Por favor, intente nuevamente.');
        }
    }
}
