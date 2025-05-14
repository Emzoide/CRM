<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Tienda;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::with('permisos')->get();
        $tiendas = Tienda::all();
        $permisos = Permiso::all();
        return view('admin.roles.index', compact('roles', 'tiendas', 'permisos'));
    }

    public function store(Request $request)
    {
        // Log detallado de los datos recibidos para debugging
        Log::info('STORE - Datos recibidos en la solicitud:', [
            'all_data' => $request->all(),
            'puede_gestionar_roles' => [
                'value' => $request->puede_gestionar_roles,
                'type' => gettype($request->puede_gestionar_roles),
                'is_array' => is_array($request->puede_gestionar_roles),
                'is_string' => is_string($request->puede_gestionar_roles),
                'is_null' => is_null($request->puede_gestionar_roles),
                'raw' => $request->input('puede_gestionar_roles'),
                'json' => $request->input('roles_seleccionados')
            ],
            'puede_gestionar_tiendas' => [
                'value' => $request->puede_gestionar_tiendas,
                'type' => gettype($request->puede_gestionar_tiendas),
                'is_array' => is_array($request->puede_gestionar_tiendas),
                'is_string' => is_string($request->puede_gestionar_tiendas),
                'is_null' => is_null($request->puede_gestionar_tiendas),
                'raw' => $request->input('puede_gestionar_tiendas'),
                'json' => $request->input('tiendas_seleccionadas')
            ]
        ]);
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles',
            'descripcion' => 'required|string',
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id',
            'is_admin' => 'boolean',
            'puede_gestionar_roles' => 'nullable|array',
            'puede_gestionar_roles.*' => 'exists:roles,id',
            'puede_gestionar_tiendas' => 'nullable|array',
            'puede_gestionar_tiendas.*' => 'exists:tiendas,id'
        ]);
        try {
            $rol = new Rol();
            $rol->nombre = $request->nombre;
            $rol->descripcion = $request->descripcion;
            // Normalizar puede_gestionar_roles
            $rolesGestion = $request->puede_gestionar_roles;
            
            // Si puede_gestionar_roles es null pero roles_seleccionados existe, usar ese valor
            if (is_null($rolesGestion) && $request->has('roles_seleccionados')) {
                $rolesGestion = $request->input('roles_seleccionados');
                Log::info('STORE - Usando roles_seleccionados en lugar de puede_gestionar_roles', [
                    'roles_seleccionados' => $rolesGestion
                ]);
            }
            
            if (is_string($rolesGestion)) {
                $rolesGestion = json_decode($rolesGestion, true);
            }
            $rol->puede_gestionar_roles = is_array($rolesGestion) ? $rolesGestion : [];

            // Normalizar puede_gestionar_tiendas
            $tiendasGestion = $request->puede_gestionar_tiendas;
            
            // Si puede_gestionar_tiendas es null pero tiendas_seleccionadas existe, usar ese valor
            if (is_null($tiendasGestion) && $request->has('tiendas_seleccionadas')) {
                $tiendasGestion = $request->input('tiendas_seleccionadas');
                Log::info('STORE - Usando tiendas_seleccionadas en lugar de puede_gestionar_tiendas', [
                    'tiendas_seleccionadas' => $tiendasGestion
                ]);
            }
            
            if (is_string($tiendasGestion)) {
                $tiendasGestion = json_decode($tiendasGestion, true);
            }
            $rol->puede_gestionar_tiendas = is_array($tiendasGestion) ? $tiendasGestion : [];
            $rol->save();

            // Asignar permisos
            if (!$rol->is_admin && $request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            }

            Log::info('Rol creado', [
                'rol_id' => $rol->id,
                'creado_por' => auth()->id(),
                'request' => $request->all(),
                'puede_gestionar_roles' => [
                    'value' => $rol->puede_gestionar_roles,
                    'type' => gettype($rol->puede_gestionar_roles),
                    'is_array' => is_array($rol->puede_gestionar_roles),
                    'count' => is_array($rol->puede_gestionar_roles) ? count($rol->puede_gestionar_roles) : 0,
                ],
                'puede_gestionar_tiendas' => [
                    'value' => $rol->puede_gestionar_tiendas,
                    'type' => gettype($rol->puede_gestionar_tiendas),
                    'is_array' => is_array($rol->puede_gestionar_tiendas),
                    'count' => is_array($rol->puede_gestionar_tiendas) ? count($rol->puede_gestionar_tiendas) : 0,
                ],
                'roles_seleccionados' => $request->input('roles_seleccionados'),
                'tiendas_seleccionadas' => $request->input('tiendas_seleccionadas')
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rol creado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al crear rol', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return back()->with('error', 'Error al crear el rol: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, Rol $rol)
    {
        // Log detallado de los datos recibidos para debugging
        Log::info('UPDATE - Datos recibidos en la solicitud:', [
            'rol_id' => $rol->id,
            'all_data' => $request->all(),
            'puede_gestionar_roles' => [
                'value' => $request->puede_gestionar_roles,
                'type' => gettype($request->puede_gestionar_roles),
                'is_array' => is_array($request->puede_gestionar_roles),
                'is_string' => is_string($request->puede_gestionar_roles),
                'is_null' => is_null($request->puede_gestionar_roles),
                'raw' => $request->input('puede_gestionar_roles'),
                'json' => $request->input('roles_seleccionados')
            ],
            'puede_gestionar_tiendas' => [
                'value' => $request->puede_gestionar_tiendas,
                'type' => gettype($request->puede_gestionar_tiendas),
                'is_array' => is_array($request->puede_gestionar_tiendas),
                'is_string' => is_string($request->puede_gestionar_tiendas),
                'is_null' => is_null($request->puede_gestionar_tiendas),
                'raw' => $request->input('puede_gestionar_tiendas'),
                'json' => $request->input('tiendas_seleccionadas')
            ]
        ]);
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $rol->id,
            'descripcion' => 'required|string',
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id',
            'is_admin' => 'boolean',
            'puede_gestionar_roles' => 'nullable|array',
            'puede_gestionar_roles.*' => 'exists:roles,id',
            'puede_gestionar_tiendas' => 'nullable|array',
            'puede_gestionar_tiendas.*' => 'exists:tiendas,id'
        ]);

        try {
            $rol->nombre = $request->nombre;
            $rol->descripcion = $request->descripcion;  
            $rol->is_admin = $request->is_admin ?? false;
            
            // Normalizar puede_gestionar_roles
            $rolesGestion = $request->puede_gestionar_roles;
            
            // Si puede_gestionar_roles es null pero roles_seleccionados existe, usar ese valor
            if (is_null($rolesGestion) && $request->has('roles_seleccionados')) {
                $rolesGestion = $request->input('roles_seleccionados');
                Log::info('UPDATE - Usando roles_seleccionados en lugar de puede_gestionar_roles', [
                    'roles_seleccionados' => $rolesGestion
                ]);
            }
            
            Log::info('UPDATE - Roles antes de normalizar:', [
                'value' => $rolesGestion,
                'type' => gettype($rolesGestion),
                'roles_seleccionados_input' => $request->input('roles_seleccionados')
            ]);
            
            if (is_string($rolesGestion)) {
                Log::info('UPDATE - Decodificando roles desde string', ['original' => $rolesGestion]);
                $rolesGestion = json_decode($rolesGestion, true);
                Log::info('UPDATE - Roles decodificados', ['resultado' => $rolesGestion]);
            }
            
            $rol->puede_gestionar_roles = is_array($rolesGestion) ? $rolesGestion : [];
            Log::info('UPDATE - Roles finales asignados', [
                'roles_final' => $rol->puede_gestionar_roles, 
                'tipo' => gettype($rol->puede_gestionar_roles),
                'count' => count($rol->puede_gestionar_roles)
            ]);

            // Normalizar puede_gestionar_tiendas
            $tiendasGestion = $request->puede_gestionar_tiendas;
            
            // Si puede_gestionar_tiendas es null pero tiendas_seleccionadas existe, usar ese valor
            if (is_null($tiendasGestion) && $request->has('tiendas_seleccionadas')) {
                $tiendasGestion = $request->input('tiendas_seleccionadas');
                Log::info('UPDATE - Usando tiendas_seleccionadas en lugar de puede_gestionar_tiendas', [
                    'tiendas_seleccionadas' => $tiendasGestion
                ]);
            }
            
            Log::info('UPDATE - Tiendas antes de normalizar:', [
                'value' => $tiendasGestion,
                'type' => gettype($tiendasGestion),
                'tiendas_seleccionadas_input' => $request->input('tiendas_seleccionadas')
            ]);
            
            if (is_string($tiendasGestion)) {
                Log::info('UPDATE - Decodificando tiendas desde string', ['original' => $tiendasGestion]);
                $tiendasGestion = json_decode($tiendasGestion, true);
                Log::info('UPDATE - Tiendas decodificadas', ['resultado' => $tiendasGestion]);
            }
            
            $rol->puede_gestionar_tiendas = is_array($tiendasGestion) ? $tiendasGestion : [];
            Log::info('UPDATE - Tiendas finales asignadas', [
                'tiendas_final' => $rol->puede_gestionar_tiendas, 
                'tipo' => gettype($rol->puede_gestionar_tiendas),
                'count' => count($rol->puede_gestionar_tiendas)
            ]);
            $rol->save();             

            // Actualizar permisos
            if (!$rol->is_admin && $request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            } else if ($rol->is_admin) {
                $rol->permisos()->detach();
            }

            Log::info('Rol actualizado', [
                'rol_id' => $rol->id,
                'actualizado_por' => auth()->id(),
                'request' => $request->all(),
                'puede_gestionar_roles' => [
                    'value' => $rol->puede_gestionar_roles,
                    'type' => gettype($rol->puede_gestionar_roles),
                    'is_array' => is_array($rol->puede_gestionar_roles),
                    'count' => is_array($rol->puede_gestionar_roles) ? count($rol->puede_gestionar_roles) : 0,
                ],
                'puede_gestionar_tiendas' => [
                    'value' => $rol->puede_gestionar_tiendas,
                    'type' => gettype($rol->puede_gestionar_tiendas),
                    'is_array' => is_array($rol->puede_gestionar_tiendas),
                    'count' => is_array($rol->puede_gestionar_tiendas) ? count($rol->puede_gestionar_tiendas) : 0,
                ],
                'roles_seleccionados' => $request->input('roles_seleccionados'),
                'tiendas_seleccionadas' => $request->input('tiendas_seleccionadas')
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rol actualizado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar rol', [
                'error' => $e->getMessage(),
                'rol_id' => $rol->id,
                'request' => $request->all()
            ]);

            return back()->with('error', 'Error al actualizar el rol: ' . $e->getMessage())
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

    public function show(Rol $rol)
    {
        return response()->json([
            'id' => $rol->id,
            'nombre' => $rol->nombre,
            'descripcion' => $rol->descripcion,
            'is_admin' => $rol->is_admin,
            'permisos' => $rol->permisos->pluck('id'),
            'puede_gestionar_roles' => $rol->puede_gestionar_roles,
            'puede_gestionar_tiendas' => $rol->puede_gestionar_tiendas
        ]);
    }
}
