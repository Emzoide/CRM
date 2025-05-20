<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FiltroConfiguracion extends Model
{
    protected $table = 'filtros_configuracion';
    
    protected $fillable = [
        'nombre',
        'rol_id',
        'usuario_id',
        'es_predeterminado',
        'configuracion',
        'orden',
    ];
    
    protected $casts = [
        'configuracion' => 'array',
        'es_predeterminado' => 'boolean',
    ];
    
    /**
     * Relación con el rol al que pertenece este filtro
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
    
    /**
     * Relación con el usuario que creó este filtro
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    
    /**
     * Scope para filtros públicos (disponibles para todos los usuarios del rol)
     */
    public function scopePublicos($query)
    {
        return $query->whereNull('usuario_id');
    }
    
    /**
     * Scope para filtros personales de un usuario específico
     */
    public function scopePersonales($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
    
    /**
     * Scope para filtros disponibles para un usuario
     * (incluye filtros públicos de sus roles y sus filtros personales)
     */
    public function scopeDisponiblesParaUsuario($query, $usuario)
    {
        $rolesIds = DB::table('usuario_rol')
            ->where('usuario_id', $usuario->id)
            ->pluck('rol_id')
            ->toArray();
            
        return $query->where(function($q) use ($usuario, $rolesIds) {
            $q->whereIn('rol_id', $rolesIds)
              ->whereNull('usuario_id')
              ->orWhere('usuario_id', $usuario->id);
        });
    }
    
    /**
     * Scope para filtros predeterminados
     */
    public function scopePredeterminados($query)
    {
        return $query->where('es_predeterminado', true);
    }
    
    /**
     * Obtener el filtro predeterminado para un usuario
     */
    public static function obtenerPredeterminadoParaUsuario($usuario)
    {
        // Primero busca un filtro personal predeterminado
        $filtro = self::personales($usuario->id)
            ->predeterminados()
            ->first();
        
        // Si no hay filtro personal, busca un filtro de rol predeterminado
        if (!$filtro) {
            $rolesIds = DB::table('usuario_rol')
                ->where('usuario_id', $usuario->id)
                ->pluck('rol_id')
                ->toArray();
                
            $filtro = self::whereIn('rol_id', $rolesIds)
                ->whereNull('usuario_id')
                ->predeterminados()
                ->first();
        }
        
        return $filtro;
    }
    
    /**
     * Aplicar esta configuración de filtro a una consulta de clientes
     */
    public function aplicarAConsulta($query)
    {
        if (empty($this->configuracion)) {
            return $query;
        }
        
        $query = app(FiltroClienteService::class)->aplicarFiltros($query, $this->configuracion);
        
        return $query;
    }
}
