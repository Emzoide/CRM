<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use App\Models\Rol;

/**
 * Sistema de Gestión de Usuarios
 * 
 * Jerarquía de permisos:
 * 1. ADMINISTRADOR (gestionar_usuarios)
 *    - Puede gestionar a TODOS los usuarios
 * 
 * 2. JEFE DE TIENDA (gestionar_usuarios_tienda)
 *    - Puede gestionar solo usuarios de SU tienda
 *    - Solo puede gestionar ciertos roles (ej: ASESOR DE VENTAS)
 *    - NO puede gestionar a otros administradores
 * 
 * 3. GESTOR DE ROL (gestionar_usuarios_rol)
 *    - Puede gestionar usuarios con su mismo rol
 * 
 * Configuración:
 * - Cada usuario tiene UN solo rol
 * - Los permisos se asignan a los roles, no a los usuarios directamente
 * - Los permisos se cachean por 24 horas
 */
class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    // Campos fillables
    protected $fillable = [
        'login',
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'tienda_id',
        'sucursal_id',
        'rol_id',
        'activo',
        'last_login',
        'remember_token',
        'full_name', // Agregado para permitir guardar el nombre completo
    ];

    // Campos ocultos al serializar
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // Casts y fechas
    protected $casts = [
        'activo'     => 'boolean',
        'last_login' => 'datetime',
    ];

    // Atributos virtuales para serializar
    protected $appends = [
        'full_name',
    ];

    // Relaciones
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'vendedor_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'usuario_id');
    }

    // Obtiene el rol principal (para compatibilidad con código existente)
    public function getRolAttribute()
    {
        // Usar consulta directa en vez de relación
        $rol = DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->select('roles.*')
            ->first();
            
        // Convertir a modelo si existe
        if ($rol) {
            return Rol::find($rol->id);
        }
        
        return null;
    }

    // Relación con roles (mantener para compatibilidad)
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol');
    }

    // Accessor: concatena first_name + last_name
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Para que Laravel use `password_hash` como password
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Obtiene la clave para el caché de permisos
     */
    protected function getPermisosCacheKey()
    {
        return "user.{$this->id}.permisos";
    }

    /**
     * Obtiene los permisos del usuario con caché
     */
    public function getPermisos()
    {
        return Cache::remember($this->getPermisosCacheKey(), now()->addDay(), function () {
            // Consulta directa a la base de datos
            $permisos = DB::table('usuario_rol')
                ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
                ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
                ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
                ->where('usuario_rol.usuario_id', $this->id)
                ->pluck('permisos.nombre')
                ->toArray();
                
            return $permisos;
        });
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function tienePermiso($permiso): bool
    {
        Log::channel('daily')->info("Verificando permiso '$permiso' para usuario ID: {$this->id} ({$this->email})");
        
        // Obtener y loguear los roles del usuario
        $roles = DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->select('roles.id', 'roles.nombre', 'roles.is_admin')
            ->get();
            
        $rolesInfo = [];
        foreach ($roles as $rol) {
            $rolesInfo[] = "[ID: {$rol->id}, Nombre: {$rol->nombre}, Es Admin: " . ($rol->is_admin ? 'Sí' : 'No') . "]";
        }
        
        Log::channel('daily')->info("Roles del usuario ID {$this->id}: " . implode(", ", $rolesInfo));
        
        // Comprobar si alguno de los roles es admin
        $isAdmin = $roles->where('is_admin', true)->count() > 0;
        if ($isAdmin) {
            Log::channel('daily')->info("Usuario ID {$this->id} tiene rol de admin - Permiso '$permiso' CONCEDIDO");
            return true;
        }
        
        // Comprobar permiso específico
        $permisoEncontrado = DB::table('usuario_rol')
            ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
            ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->where('permisos.nombre', $permiso)
            ->exists();
            
        Log::channel('daily')->info("Permiso '$permiso' para usuario ID {$this->id}: " . 
            ($permisoEncontrado ? 'CONCEDIDO' : 'DENEGADO'));
            
        return $permisoEncontrado;
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     */
    public function tienePermisos($permisos): bool
    {
        if (!is_array($permisos)) {
            $permisos = [$permisos];
        }

        // Verificar si es admin primero
        $isAdmin = DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->where('roles.is_admin', true)
            ->exists();
            
        if ($isAdmin) {
            return true;
        }

        // Verificar cada permiso requerido
        foreach ($permisos as $permiso) {
            if (!$this->tienePermiso($permiso)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos especificados
     */
    public function tieneAlgunPermiso($permisos): bool
    {
        if (!is_array($permisos)) {
            $permisos = [$permisos];
        }

        // Comprobación directa en la base de datos utilizando joins
        $tienePermiso = DB::table('usuario_rol')
            ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
            ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->where(function($query) use ($permisos) {
                $query->whereIn('permisos.nombre', $permisos)
                      ->orWhere('roles.is_admin', true);
            })
            ->exists();
            
        return $tienePermiso;
    }

    /**
     * Verifica si el usuario actual puede gestionar al usuario objetivo
     * 
     * @param Usuario $usuarioObjetivo
     * @return bool
     */
    public function puedeGestionarUsuario(Usuario $usuarioObjetivo): bool
    {
        // Registrar información para diagnóstico
        Log::channel('daily')->info("Verificando si usuario ID: {$this->id} puede gestionar a usuario ID: {$usuarioObjetivo->id}");
        
        // 1. Si el usuario actual es admin o tiene permiso global, puede gestionar a todos
        if ($this->tienePermiso('gestionar_usuarios') || $this->getRolAttribute()->is_admin) {
            Log::channel('daily')->info("Usuario {$this->id} es administrador o tiene permiso global");
            return true;
        }

        // 2. Si el usuario objetivo es administrador, solo otro admin puede gestionarlo
        if ($usuarioObjetivo->tienePermiso('gestionar_usuarios') || 
            ($usuarioObjetivo->getRolAttribute() && $usuarioObjetivo->getRolAttribute()->is_admin)) {
            Log::channel('daily')->info("Objetivo {$usuarioObjetivo->id} es administrador - acceso denegado");
            return false;
        }
        
        // 3. Verificar si mi rol puede gestionar el rol del usuario objetivo
        $miRol = $this->getRolAttribute();
        $rolObjetivo = $usuarioObjetivo->getRolAttribute();
        
        if ($miRol && $rolObjetivo) {
            // Si mi rol tiene permiso para gestionar el rol del objetivo
            if ($miRol->puedeGestionarRol($rolObjetivo->id)) {
                Log::channel('daily')->info("Usuario {$this->id} (rol {$miRol->id}) puede gestionar rol {$rolObjetivo->id}");
                
                // 4. Verificar si mi rol puede gestionar la tienda del objetivo
                if ($miRol->puedeGestionarTienda($usuarioObjetivo->tienda_id)) {
                    Log::channel('daily')->info("Usuario {$this->id} puede gestionar tienda {$usuarioObjetivo->tienda_id}");
                    return true;
                }
                
                // 5. O verificar si tenemos la misma tienda (gestión local)
                if ($this->tienda_id === $usuarioObjetivo->tienda_id) {
                    Log::channel('daily')->info("Usuario {$this->id} y {$usuarioObjetivo->id} pertenecen a la misma tienda {$this->tienda_id}");
                    return true;
                }
                
                // 6. O verificar si tenemos la misma sucursal (gestión por sucursal)
                if ($this->sucursal_id === $usuarioObjetivo->sucursal_id) {
                    Log::channel('daily')->info("Usuario {$this->id} y {$usuarioObjetivo->id} pertenecen a la misma sucursal {$this->sucursal_id}");
                    return true;
                }
            }
        }
        
        // 7. Compatibilidad con sistema anterior: gestión por tienda
        if ($this->tienePermiso('gestionar_usuarios_tienda') && 
            $this->tienda_id === $usuarioObjetivo->tienda_id) {
            
            // Verificar que el rol del objetivo sea gestionable por este usuario
            $rolObjetivo = $usuarioObjetivo->getRolAttribute();
            $rolesGestionables = ['ASESOR DE VENTAS', 'ASISTENTE DE VENTAS'];
            
            if ($rolObjetivo && in_array(strtoupper($rolObjetivo->nombre), $rolesGestionables)) {
                Log::channel('daily')->info("Usuario {$this->id} puede gestionar a {$usuarioObjetivo->id} en misma tienda como {$rolObjetivo->nombre}");
                return true;
            }
        }

        // 8. Compatibilidad con sistema anterior: gestión por rol
        if ($this->tienePermiso('gestionar_usuarios_rol')) {
            // Verificar si hay algún rol en común
            $tieneRolComun = DB::table('usuario_rol as ur1')
                ->join('usuario_rol as ur2', 'ur1.rol_id', '=', 'ur2.rol_id')
                ->where('ur1.usuario_id', $this->id)
                ->where('ur2.usuario_id', $usuarioObjetivo->id)
                ->exists();
                
            if ($tieneRolComun) {
                Log::channel('daily')->info("Usuario {$this->id} puede gestionar a {$usuarioObjetivo->id} por rol común");
                return true;
            }
        }

        Log::channel('daily')->info("Usuario {$this->id} NO PUEDE gestionar a {$usuarioObjetivo->id}");
        return false;
    }

    /**
     * Verifica si el usuario tiene un rol específico por su nombre
     * 
     * @param string $rolNombre
     * @return bool
     */
    public function hasRole($rolNombre): bool
    {
        return DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $this->id)
            ->where('roles.nombre', strtoupper($rolNombre))
            ->exists();
    }

    /**
     * Limpiar caché cuando se actualiza el usuario o su rol
     */
    protected static function booted()
    {
        static::saved(function ($usuario) {
            Cache::forget($usuario->getPermisosCacheKey());
        });
        
        static::deleted(function ($usuario) {
            Cache::forget($usuario->getPermisosCacheKey());
        });
    }
}
