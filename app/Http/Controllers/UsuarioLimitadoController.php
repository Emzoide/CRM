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

class UsuarioLimitadoController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la vista limitada para gestión de usuarios
     */
    public function index()
    {
        $usuarioActual = auth()->user();
        $query = Usuario::with(['tienda', 'roles'])
            ->where('id', '!=', $usuarioActual->id); // Excluir al usuario actual
        
        // Filtrar por tienda si el usuario tiene permiso para gestionar usuarios de su tienda
        if ($usuarioActual->tienePermiso('gestionar_usuarios_tienda') && $usuarioActual->tienda_id) {
            $query->where('tienda_id', $usuarioActual->tienda_id);
        }
        
        // Filtrar por rol si el usuario tiene permiso para gestionar usuarios de su mismo rol
        if ($usuarioActual->tienePermiso('gestionar_usuarios_rol')) {
            $rolUsuario = $usuarioActual->getRolAttribute();
            if ($rolUsuario) {
                $usuariosConMismoRol = DB::table('usuario_rol')
                    ->where('rol_id', $rolUsuario->id)
                    ->pluck('usuario_id')
                    ->toArray();
                $query->whereIn('id', $usuariosConMismoRol);
            }
        }
        
        $usuarios = $query->get();
        
        // Obtener solo los roles que el usuario puede gestionar
        $rolesQuery = Rol::query();
        
        if ($usuarioActual->tienePermiso('gestionar_usuarios_rol')) {
            $rolUsuario = $usuarioActual->getRolAttribute();
            if ($rolUsuario) {
                $rolesQuery->where('id', $rolUsuario->id);
            }
        } else {
            // Si no tiene el permiso para gestionar roles, no debería ver ninguno
            $rolesQuery->where('id', 0); // Esto asegura que no se devuelva ningún rol
        }
        
        $roles = $rolesQuery->get();
        
        // Obtener solo las tiendas que el usuario puede gestionar
        $tiendasQuery = Tienda::with('sucursal');
        
        if ($usuarioActual->tienePermiso('gestionar_usuarios_tienda')) {
            if ($usuarioActual->tienda_id) {
                $tiendasQuery->where('id', $usuarioActual->tienda_id);
            }
        } else {
            // Si no tiene el permiso para gestionar tiendas, no debería ver ninguna
            $tiendasQuery->where('id', 0); // Esto asegura que no se devuelva ninguna tienda
        }
        
        $tiendas = $tiendasQuery->orderBy('nombre')->get();
        
        return View::make('admin.usuarios-limitado', compact('usuarios', 'roles', 'tiendas'));
    }
    
    /**
     * Almacena un nuevo usuario con permisos limitados
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
            'roles' => 'required|array',
        ]);
        
        // Validar que los roles seleccionados son los que el usuario puede gestionar
        foreach ($data['roles'] as $rolId) {
            $rol = Rol::find($rolId);
            if (!$rol || !$this->puedeAsignarRol($rol)) {
                return Redirect::back()
                    ->with('error', 'No tienes permiso para asignar el rol seleccionado.')
                    ->withInput();
            }
        }
        
        // Validar la tienda si se especificó
        if ($request->has('tienda_id') && $request->tienda_id) {
            if (!$this->puedeAsignarTienda($request->tienda_id)) {
                return Redirect::back()
                    ->with('error', 'No tienes permiso para asignar la tienda seleccionada.')
                    ->withInput();
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Crear usuario
            $usuario = Usuario::create([
                'login' => $data['login'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tienda_id' => $user->tienePermiso('gestionar_usuarios_tienda') ? $user->tienda_id : null,
                'activo' => true,
            ]);
            
            // Asignar roles
            $usuario->roles()->attach($data['roles']);
            
            DB::commit();
            
            return Redirect::route('admin.usuarios.limitado')
                ->with('success', 'Usuario creado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear usuario: " . $e->getMessage());
            Log::error("Datos enviados: ", $data);
            
            return Redirect::back()
                ->with('error', 'Error al crear el usuario. Por favor, intente nuevamente.')
                ->withInput();
        }
    }
    
    /**
     * Actualiza un usuario existente con permisos limitados
     */
    public function update(Request $request, Usuario $usuario)
    {
        $user = auth()->user();
        
        // Verificar si el usuario actual puede gestionar al usuario objetivo
        if (!$this->puedeGestionarUsuario($usuario)) {
            return Redirect::back()
                ->with('error', 'No tienes permiso para editar este usuario.');
        }
        
        // Validar datos
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email,' . $usuario->id,
            'roles' => 'required|array',
            'activo' => 'boolean',
        ]);
        
        // Validar que los roles seleccionados son los que el usuario puede gestionar
        foreach ($data['roles'] as $rolId) {
            $rol = Rol::find($rolId);
            if (!$rol || !$this->puedeAsignarRol($rol)) {
                return Redirect::back()
                    ->with('error', 'No tienes permiso para asignar el rol seleccionado.')
                    ->withInput();
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Actualizar usuario
            $usuario->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'activo' => $request->has('activo'),
            ]);
            
            // Si el usuario puede gestionar usuarios de su tienda, asignar su tienda
            if ($user->tienePermiso('gestionar_usuarios_tienda')) {
                $usuario->tienda_id = $user->tienda_id;
                $usuario->save();
            }
            
            // Actualizar roles
            $usuario->roles()->sync($data['roles']);
            
            DB::commit();
            
            return Redirect::route('admin.usuarios.limitado')
                ->with('success', 'Usuario actualizado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar usuario: " . $e->getMessage());
            Log::error("Datos enviados: ", $data);
            
            return Redirect::back()
                ->with('error', 'Error al actualizar el usuario. Por favor, intente nuevamente.')
                ->withInput();
        }
    }
    
    /**
     * Desactiva un usuario (eliminación lógica)
     */
    public function destroy(Usuario $usuario)
    {
        $user = auth()->user();
        
        // Verificar si el usuario actual puede gestionar al usuario objetivo
        if (!$this->puedeGestionarUsuario($usuario)) {
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
            
            return Redirect::route('admin.usuarios.limitado')
                ->with('success', 'Usuario desactivado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error("Error al desactivar usuario: " . $e->getMessage());
            
            return Redirect::back()
                ->with('error', 'Error al desactivar el usuario. Por favor, intente nuevamente.');
        }
    }
    
    /**
     * Verifica si el usuario actual puede gestionar al usuario dado
     */
    protected function puedeGestionarUsuario(Usuario $usuario): bool
    {
        $user = auth()->user();
        
        // No permitir gestionar a sí mismo
        if ($user->id === $usuario->id) {
            return false;
        }
        
        // Si puede gestionar usuarios de su tienda, verificar si el usuario es de su tienda
        if ($user->tienePermiso('gestionar_usuarios_tienda')) {
            return $user->tienda_id === $usuario->tienda_id;
        }
        
        // Si puede gestionar usuarios de su mismo rol, verificar si el usuario tiene su mismo rol
        if ($user->tienePermiso('gestionar_usuarios_rol')) {
            $rolUsuario = $user->getRolAttribute();
            if ($rolUsuario) {
                return $usuario->roles()->where('rol_id', $rolUsuario->id)->exists();
            }
        }
        
        return false;
    }
    
    /**
     * Verifica si el usuario actual puede asignar un rol específico
     */
    protected function puedeAsignarRol(Rol $rol): bool
    {
        $user = auth()->user();
        
        // Si puede gestionar usuarios de su mismo rol, solo puede asignar su rol
        if ($user->tienePermiso('gestionar_usuarios_rol')) {
            $rolUsuario = $user->getRolAttribute();
            return $rolUsuario && $rol->id === $rolUsuario->id;
        }
        
        return false;
    }
    
    /**
     * Verifica si el usuario actual puede asignar una tienda específica
     */
    protected function puedeAsignarTienda($tiendaId): bool
    {
        $user = auth()->user();
        
        // Si puede gestionar usuarios de su tienda, solo puede asignar su tienda
        if ($user->tienePermiso('gestionar_usuarios_tienda')) {
            return $user->tienda_id == $tiendaId;
        }
        
        return false;
    }
}
