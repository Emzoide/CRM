<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Tienda;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    /**
     * Muestra la lista de usuarios
     * Filtra los usuarios según los permisos del usuario autenticado
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuarioActual = auth()->user();
        $query = Usuario::with(['tienda', 'roles'])
            ->where('id', '!=', $usuarioActual->id); // Excluir al usuario actual

        $tieneAccesoCompleto = $usuarioActual->tienePermiso('gestionar_usuarios');
        $puedeGestionarTienda = $usuarioActual->tienePermiso('gestionar_usuarios_tienda');
        $puedeGestionarRol = $usuarioActual->tienePermiso('gestionar_usuarios_rol');

        // Si el usuario no tiene acceso completo, aplicar filtros
        if (!$tieneAccesoCompleto) {
            $condicionesCumplidas = false;
            
            // IMPORTANTE: Excluir SIEMPRE a los usuarios administradores
            // Los usuarios no-admin nunca deberían ver a los administradores
            $query->whereDoesntHave('roles', function($q) {
                $q->where('roles.is_admin', true);
            });
            
            // NUEVO ENFOQUE: Crear un query builder más estricto
            // Si hay múltiples condiciones, el usuario debe cumplir TODAS ellas, no solo alguna
            
            // Filtro 1: Por tiendas gestionables
            if ($puedeGestionarTienda) {
                $rolUsuario = $usuarioActual->getRolAttribute();
                
                if ($rolUsuario) {
                    // Obtener los IDs de las tiendas que puede gestionar
                    $tiendasGestionablesIds = [];
                    
                    // 1. Verificar la tienda propia del usuario
                    if ($usuarioActual->tienda_id) {
                        $tiendasGestionablesIds[] = $usuarioActual->tienda_id;
                    }
                    
                    // 2. Tiendas definidas en el campo tiendas_gestionables (JSON)
                    if (is_array($rolUsuario->tiendas_gestionables)) {
                        $tiendasGestionablesIds = array_merge($tiendasGestionablesIds, $rolUsuario->tiendas_gestionables);
                    }
                    
                    // 3. Tiendas definidas en la relación many-to-many
                    $tiendasGestionablesBD = $rolUsuario->tiendasGestionables()->pluck('tiendas.id')->toArray();
                    if (!empty($tiendasGestionablesBD)) {
                        $tiendasGestionablesIds = array_merge($tiendasGestionablesIds, $tiendasGestionablesBD);
                    }
                    
                    // Aplicar el filtro si hay tiendas gestionables
                    if (!empty($tiendasGestionablesIds)) {
                        $query->whereIn('tienda_id', $tiendasGestionablesIds);
                        $condicionesCumplidas = true;
                    }
                }
            }
            
            // Filtro 2: Por roles que puede gestionar
            if ($puedeGestionarRol) {
                $rolUsuario = $usuarioActual->getRolAttribute();
                if ($rolUsuario) {
                    // Obtener los IDs de los roles que puede gestionar
                    $rolesGestionablesIds = [];
                    
                    // Roles definidos directamente en el campo roles_gestionables (JSON)
                    if (is_array($rolUsuario->roles_gestionables)) {
                        $rolesGestionablesIds = array_merge($rolesGestionablesIds, $rolUsuario->roles_gestionables);
                    }
                    
                    // Roles definidos en la relación many-to-many
                    $rolesGestionablesBD = $rolUsuario->rolesGestionables()->pluck('roles.id')->toArray();
                    if (!empty($rolesGestionablesBD)) {
                        $rolesGestionablesIds = array_merge($rolesGestionablesIds, $rolesGestionablesBD);
                    }
                    
                    // Si no hay roles gestionables explícitos, solo mostrar usuarios con el mismo rol
                    if (empty($rolesGestionablesIds)) {
                        $rolesGestionablesIds = [$rolUsuario->id];
                    }
                    
                    // Importante: Usar whereHas (no orWhereHas) para que sea una condición adicional (AND)
                    // y no una alternativa (OR)
                    $query->whereHas('roles', function($q) use ($rolesGestionablesIds) {
                        $q->whereIn('roles.id', $rolesGestionablesIds);
                    });
                    
                    $condicionesCumplidas = true;
                }
            }
            
            // Si no tiene ninguno de los permisos específicos pero llegó aquí, mostrar lista vacía
            if (!$condicionesCumplidas) {
                $query->where('id', 0); // Asegura que no devuelva resultados
            }
        }

        $usuarios = $query->get();
        $roles = $this->getRolesPermitidos();
        
        // Para las tiendas, si solo puede gestionar su tienda, mostrar solo su tienda
        $tiendasQuery = Tienda::with('sucursal');
        if ($puedeGestionarTienda && !$tieneAccesoCompleto) {
            $tiendasQuery->where('id', $usuarioActual->tienda_id);
        }
        $tiendas = $tiendasQuery->orderBy('nombre')->get();

        // Variables para la vista
        $permisos = [
            'tieneAccesoCompleto' => $tieneAccesoCompleto,
            'puedeGestionarTienda' => $puedeGestionarTienda,
            'puedeGestionarRol' => $puedeGestionarRol
        ];

        return View::make('admin.usuarios', compact('usuarios', 'roles', 'tiendas', 'permisos'));
    }

    /**
     * Obtiene los roles que el usuario actual puede asignar
     */
    protected function getRolesPermitidos()
    {
        $usuario = auth()->user();
        $query = Rol::query();
        
        // Si puede gestionar todos los usuarios, devolver todos los roles
        if ($usuario->tienePermiso('gestionar_usuarios')) {
            return $query->get();
        }
        
        // Si puede gestionar usuarios de su mismo rol, devolver solo su rol
        if ($usuario->tienePermiso('gestionar_usuarios_rol')) {
            // Obtener los roles del usuario actual
            $rolUsuario = $usuario->getRolAttribute();
            if ($rolUsuario) {
                $query->where('id', $rolUsuario->id);
            }
        }
        
        // Excluir roles de administrador si no tiene permiso
        if (!$usuario->tienePermiso('gestionar_roles')) {
            $query->where('is_admin', false);
        }
        
        return $query->get();
    }

    public function create()
    {
        return Redirect::route('admin.usuarios.index');
    }

    /**
     * Almacena un nuevo usuario en la base de datos
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Validar datos
        $data = $request->validate([
            'login' => 'required|string|max:30|unique:usuarios,login',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|exists:roles,id',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);
        
        // Guardar el rol_id para usarlo después pero quitarlo de $data para que no intente asignarlo directamente
        $rolId = $data['rol_id'];
        unset($data['rol_id']);
        
        // Verificar si puede asignar el rol seleccionado
        $rolSeleccionado = Rol::findOrFail($rolId);
        if (!$this->puedeAsignarRol($rolSeleccionado)) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para asignar este rol.')
                ->withInput();
        }

        // Verificar si puede asignar la tienda seleccionada
        if (isset($data['tienda_id']) && !$this->puedeAsignarTienda($data['tienda_id'])) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para asignar esta tienda.')
                ->withInput();
        }

        // Preparar datos del usuario
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);
        
        // Calcular nombre completo
        $data['full_name'] = trim("{$data['first_name']} {$data['last_name']}");
        
        // Asegurarse de que full_name se incluya en la consulta SQL
        DB::statement("SET sql_mode=''"); // Deshabilitar temporalmente el modo estricto para esta conexión
        
        // Asignar sucursal si se seleccionó una tienda
        if (!empty($data['tienda_id'])) {
            $tienda = Tienda::find($data['tienda_id']);
            if ($tienda) {
                $data['sucursal_id'] = $tienda->sucursal_id;
            }
        }
        
        // Marcar como activo por defecto
        $data['activo'] = 1;

        try {
            // Crear usuario
            $nuevoUsuario = Usuario::create($data);
            
            // Asociar el rol a través de la tabla pivote
            DB::table('usuario_rol')->insert([
                'usuario_id' => $nuevoUsuario->id,
                'rol_id' => $rolId
            ]);

            Log::info("Usuario creado:", [
                'id' => $nuevoUsuario->id,
                'tienda_id' => $nuevoUsuario->tienda_id,
                'sucursal_id' => $nuevoUsuario->sucursal_id,
                'email' => $nuevoUsuario->email,
                'full_name' => $nuevoUsuario->full_name,
                'rol_id' => $rolId,
                'activo' => $nuevoUsuario->activo
            ]);

            return Redirect::route('admin.usuarios.index')
                ->with('success', 'Usuario creado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error("Error al crear usuario: " . $e->getMessage());
            Log::error("Datos enviados: ", $data);
            
            return Redirect::back()
                ->with('error', 'Error al crear el usuario. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    public function show(Usuario $usuario)
    {
        return Redirect::route('admin.usuarios.index');
    }

    public function edit(Usuario $usuario)
    {
        return Redirect::route('admin.usuarios.index');
    }

    /**
     * Actualiza un usuario existente
     */
    public function update(Request $request, Usuario $usuario)
    {
        $user = auth()->user();
        
        // Verificar si el usuario actual puede gestionar al usuario objetivo
        if (!$user->puedeGestionarUsuario($usuario)) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para gestionar este usuario.');
        }

        // Validar datos
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email,' . $usuario->id,
            'rol_id' => 'required|exists:roles,id',
            'tienda_id' => 'nullable|exists:tiendas,id',
            'activo' => 'boolean',
        ]);        
        
        // Verificar si puede asignar el rol seleccionado
        $rolSeleccionado = Rol::findOrFail($data['rol_id']);
        if (!$this->puedeAsignarRol($rolSeleccionado)) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para asignar este rol.');
        }
        
        // Verificar si puede asignar la tienda seleccionada
        if (isset($data['tienda_id']) && !$this->puedeAsignarTienda($data['tienda_id'])) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para asignar esta tienda.');
        }

        // Actualizar nombre completo
        $data['full_name'] = trim("{$data['first_name']} {$data['last_name']}");

        // Asignar sucursal si se seleccionó una tienda
        if (!empty($data['tienda_id'])) {
            $tienda = Tienda::find($data['tienda_id']);
            if ($tienda) {
                $data['sucursal_id'] = $tienda->sucursal_id;
            }
        } else {
            $data['sucursal_id'] = null;
        }

        // Guardar el rol_id para usarlo después pero quitarlo de $data
        $rolId = null;
        if (isset($data['rol_id'])) {
            $rolId = $data['rol_id'];
            unset($data['rol_id']);
        }

        try {
            // Actualizar usuario
            $usuario->update($data);
            
            // Si se especificó un rol, actualizar la relación
            if ($rolId) {
                // Eliminar relaciones existentes
                DB::table('usuario_rol')->where('usuario_id', $usuario->id)->delete();
                
                // Crear nueva relación
                DB::table('usuario_rol')->insert([
                    'usuario_id' => $usuario->id,
                    'rol_id' => $rolId
                ]);
            }

            // Obtener el rol actualizado
            $rolNombre = 'No asignado';
            $rolUsuario = $usuario->getRolAttribute();
            if ($rolUsuario) {
                $rolNombre = $rolUsuario->nombre;
            }

            Log::info("Usuario actualizado:", [
                'id' => $usuario->id,
                'tienda_id' => $usuario->tienda_id,
                'sucursal_id' => $usuario->sucursal_id,
                'email' => $usuario->email,
                'full_name' => $usuario->full_name,
                'rol' => $rolNombre,
                'activo' => $usuario->activo
            ]);

            return Redirect::route('admin.usuarios.index')
                ->with('success', 'Usuario actualizado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error("Error al actualizar usuario: " . $e->getMessage());
            Log::error("Datos enviados: ", $data);
            
            return Redirect::back()
                ->with('error', 'Error al actualizar el usuario. Por favor, intente nuevamente.');
        }
    }

    /**
     * Desactiva un usuario (eliminación lógica)
     */
    public function destroy(Usuario $usuario)
    {
        $user = auth()->user();
        
        // Verificar si el usuario actual puede gestionar al usuario objetivo
        if (!$user->puedeGestionarUsuario($usuario)) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para desactivar este usuario.');
        }
        
        // No permitir desactivarse a sí mismo
        if ($user->id === $usuario->id) {
            return Redirect::back()
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        try {
            $usuario->update(['activo' => false]);

            Log::info("Usuario desactivado:", [
                'id' => $usuario->id,
                'email' => $usuario->email,
                'full_name' => $usuario->full_name,
                'desactivado_por' => $user->id
            ]);

            return Redirect::route('admin.usuarios.index')
                ->with('success', 'Usuario desactivado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error("Error al desactivar usuario: " . $e->getMessage());
            
            return Redirect::back()
                ->with('error', 'Error al desactivar el usuario. Por favor, intente nuevamente.');
        }
    }
    
    /**
     * Verifica si el usuario actual puede asignar un rol específico
     */
    protected function puedeAsignarRol(Rol $rol): bool
    {
        $user = auth()->user();
        
        // Admin puede asignar cualquier rol
        if ($user->tienePermiso('gestionar_usuarios')) {
            return true;
        }
        
        // Jefe de tienda solo puede asignar roles de asesores
        if ($user->tienePermiso('gestionar_usuarios_tienda')) {
            return in_array($rol->nombre, ['ASESOR DE VENTAS', 'ASISTENTE DE VENTAS']);
        }
        
        // Otros solo pueden asignar su mismo rol
        $rolUsuario = $user->getRolAttribute();
        return $rolUsuario && $rol->id === $rolUsuario->id;
    }
    
    /**
     * Verifica si el usuario actual puede asignar una tienda específica
     */
    protected function puedeAsignarTienda($tiendaId): bool
    {
        $user = auth()->user();
        
        // Admin puede asignar cualquier tienda
        if ($user->tienePermiso('gestionar_usuarios')) {
            return true;
        }
        
        // Jefe de tienda solo puede asignar su propia tienda
        if ($user->tienePermiso('gestionar_usuarios_tienda')) {
            return $user->tienda_id == $tiendaId;
        }
        
        // Otros no pueden asignar tiendas
        return false;
    }
}
