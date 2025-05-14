<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'is_admin',
        'tiendas_gestionables',
        'roles_gestionables',
        'niveles_acceso'
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'tiendas_gestionables' => 'array',
        'roles_gestionables' => 'array',
        'niveles_acceso' => 'array'
    ];

    // Relación con usuarios
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol');
    }

    // Relación con permisos
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso');
    }

    // Verificar si el rol tiene un permiso específico
    public function tienePermiso($permiso)
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->permisos()->where('nombre', $permiso)->exists();
    }

    // Verificar si el rol tiene todos los permisos especificados
    public function tienePermisos($permisos)
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->permisos()->whereIn('nombre', $permisos)->count() === count($permisos);
    }
    
    /**
     * Relación con las tiendas que este rol puede gestionar
     */
    public function tiendasGestionables(): BelongsToMany
    {
        return $this->belongsToMany(Tienda::class, 'rol_tienda');
    }
    
    /**
     * Relación con los roles que este rol puede gestionar
     */
    public function rolesGestionables(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_gestiona_rol', 
                                  'rol_gestor_id', 'rol_gestionado_id');
    }
    
    /**
     * Roles que pueden gestionar a este rol
     */
    public function rolesGestores(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_gestiona_rol', 
                                  'rol_gestionado_id', 'rol_gestor_id');
    }
    
    /**
     * Verifica si este rol puede gestionar el rol especificado
     */
    public function puedeGestionarRol($rolId): bool
    {
        if ($this->is_admin) {
            return true;
        }
        
        // Verificar en el array JSON
        if (is_array($this->roles_gestionables) && in_array($rolId, $this->roles_gestionables)) {
            return true;
        }
        
        // Verificar en la relación many-to-many
        return $this->rolesGestionables()->where('id', $rolId)->exists();
    }
    
    /**
     * Verifica si este rol puede gestionar la tienda especificada
     */
    public function puedeGestionarTienda($tiendaId): bool
    {
        if ($this->is_admin) {
            return true;
        }
        
        // Verificar en el array JSON
        if (is_array($this->tiendas_gestionables) && in_array($tiendaId, $this->tiendas_gestionables)) {
            return true;
        }
        
        // Verificar en la relación many-to-many
        return $this->tiendasGestionables()->where('id', $tiendaId)->exists();
    }
}
