<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class FiltroClienteService
{
    /**
     * Aplica los filtros configurados a una consulta de clientes
     * 
     * @param Builder $query La consulta de base
     * @param array $configuracion La configuración JSON decodificada
     * @return Builder La consulta con filtros aplicados
     */
    public function aplicarFiltros(Builder $query, array $configuracion): Builder
    {
        // Si hay criterios de filtrado, los aplicamos
        if (!empty($configuracion['criterios'])) {
            foreach ($configuracion['criterios'] as $criterio) {
                $query = $this->aplicarCriterio($query, $criterio);
            }
        }
        
        // Si hay ordenamiento, lo aplicamos
        if (!empty($configuracion['ordenamiento'])) {
            foreach ($configuracion['ordenamiento'] as $orden) {
                if (!empty($orden['campo']) && !empty($orden['direccion'])) {
                    $campo = $orden['campo'];
                    $direccion = strtolower($orden['direccion']) === 'desc' ? 'desc' : 'asc';
                    
                    // Para campos que requieren lógica especial
                    if ($campo === 'ultimo_seguimiento') {
                        $query->orderBy(function($q) {
                            return $q->from('seguimientos')
                                ->selectRaw('MAX(contacto_en)')
                                ->whereColumn('seguimientos.cliente_id', 'clientes.id');
                        }, $direccion);
                    } 
                    elseif ($campo === 'monto_cotizacion') {
                        $query->orderBy(function($q) {
                            return $q->from('oportunidades')
                                ->join('cotizaciones', 'cotizaciones.oportunidad_id', '=', 'oportunidades.id')
                                ->where('cotizaciones.estado', 'active')
                                ->whereColumn('oportunidades.cliente_id', 'clientes.id')
                                ->selectRaw('MAX(cotizaciones.total)');
                        }, $direccion);
                    }
                    elseif ($campo === 'probabilidad') {
                        $query->orderBy(function($q) {
                            return $q->from('oportunidades')
                                ->whereColumn('oportunidades.cliente_id', 'clientes.id')
                                ->whereNotIn('etapa_actual', ['won', 'lost'])
                                ->selectRaw('MAX(probabilidad)');
                        }, $direccion);
                    }
                    else {
                        // Campos normales
                        $query->orderBy($campo, $direccion);
                    }
                }
            }
        }
        
        // Aplicar límite si está configurado
        if (!empty($configuracion['limite']) && is_numeric($configuracion['limite'])) {
            $query->limit(intval($configuracion['limite']));
        }
        
        return $query;
    }
    
    /**
     * Aplica un criterio individual de filtrado
     * 
     * @param Builder $query La consulta
     * @param array $criterio El criterio individual
     * @return Builder La consulta con el criterio aplicado
     */
    protected function aplicarCriterio(Builder $query, array $criterio): Builder
    {
        if (empty($criterio['campo']) || empty($criterio['operador'])) {
            return $query;
        }
        
        $campo = $criterio['campo'];
        $operador = $criterio['operador'];
        $valor = $criterio['valor'] ?? null;
        $tipo = $criterio['tipo'] ?? 'texto';
        
        try {
            switch ($campo) {
                case 'ultimo_seguimiento':
                    return $this->filtrarPorUltimoSeguimiento($query, $operador, $valor, $tipo);
                    
                case 'cotizacion_activa':
                    return $this->filtrarPorCotizacionActiva($query, $operador, $valor);
                    
                case 'asignado_a':
                    return $this->filtrarPorAsignacion($query, $operador, $valor);
                    
                case 'monto_cotizacion':
                    return $this->filtrarPorMontoCotizacion($query, $operador, $valor);
                    
                case 'probabilidad':
                    return $this->filtrarPorProbabilidad($query, $operador, $valor);
                    
                case 'tienda_id':
                    return $this->filtrarPorTienda($query, $operador, $valor);
                    
                case 'rol_vendedor':
                    return $this->filtrarPorRolVendedor($query, $operador, $valor);
                    
                default:
                    // Campos directos de la tabla clientes
                    if (in_array($campo, (new Cliente)->getFillable())) {
                        // Para timestamps y fechas
                        if ($tipo === 'fecha' && $valor) {
                            if (strpos($valor, 'd') !== false) { // es un periodo relativo como "5d"
                                $dias = intval($valor);
                                $fecha = Carbon::now()->subDays($dias);
                                
                                if ($operador === '>') {
                                    return $query->where($campo, '>', $fecha);
                                } elseif ($operador === '<') {
                                    return $query->where($campo, '<', $fecha);
                                } elseif ($operador === '<=') {
                                    return $query->where($campo, '<=', $fecha);
                                } elseif ($operador === '>=') {
                                    return $query->where($campo, '>=', $fecha);
                                }
                            } else { // es una fecha específica
                                return $query->where($campo, $operador, $valor);
                            }
                        } 
                        // Para texto
                        elseif ($tipo === 'texto') {
                            if ($operador === 'contiene') {
                                return $query->where($campo, 'like', "%{$valor}%");
                            } elseif ($operador === 'empieza_con') {
                                return $query->where($campo, 'like', "{$valor}%");
                            } elseif ($operador === 'termina_con') {
                                return $query->where($campo, 'like', "%{$valor}");
                            } else {
                                return $query->where($campo, $operador, $valor);
                            }
                        } 
                        // Para números y otros
                        else {
                            return $query->where($campo, $operador, $valor);
                        }
                    }
            }
        } catch (\Exception $e) {
            Log::error("Error al aplicar filtro: " . $e->getMessage(), [
                'criterio' => $criterio
            ]);
        }
        
        return $query;
    }
    
    /**
     * Filtra por fecha del último seguimiento
     */
    protected function filtrarPorUltimoSeguimiento(Builder $query, string $operador, $valor, string $tipo): Builder
    {
        if ($tipo === 'tiempo' && strpos($valor, 'd') !== false) {
            $dias = intval($valor);
            $fechaLimite = Carbon::now()->subDays($dias);
            
            // Subconsulta para obtener la fecha del último seguimiento por cliente
            $subquery = \DB::table('seguimientos')
                ->select('cliente_id', \DB::raw('MAX(contacto_en) as ultima_fecha'))
                ->groupBy('cliente_id');
                
            // Join con la subconsulta
            $query->joinSub($subquery, 'ultimo_seguimiento', function($join) {
                $join->on('clientes.id', '=', 'ultimo_seguimiento.cliente_id');
            });
            
            // Comparación usando el operador proporcionado
            if ($operador === '>') {
                $query->where('ultimo_seguimiento.ultima_fecha', '<', $fechaLimite); // Invertimos la lógica
            } elseif ($operador === '<') {
                $query->where('ultimo_seguimiento.ultima_fecha', '>', $fechaLimite); // Invertimos la lógica
            } elseif ($operador === '>=') {
                $query->where('ultimo_seguimiento.ultima_fecha', '<=', $fechaLimite); // Invertimos la lógica
            } elseif ($operador === '<=') {
                $query->where('ultimo_seguimiento.ultima_fecha', '>=', $fechaLimite); // Invertimos la lógica
            }
        }
        
        return $query;
    }
    
    /**
     * Filtra por existencia de cotización activa
     */
    protected function filtrarPorCotizacionActiva(Builder $query, string $operador, $valor): Builder
    {
        $existeCotizacion = $valor === true || $valor === 'true' || $valor === 1 || $valor === '1';
        
        // Subconsulta para clientes con cotizaciones activas
        $subquery = \DB::table('oportunidades')
            ->join('cotizaciones', 'oportunidades.id', '=', 'cotizaciones.oportunidad_id')
            ->where('cotizaciones.estado', 'active')
            ->select('oportunidades.cliente_id')
            ->distinct();
            
        if ($operador === '=' && $existeCotizacion) {
            // Clientes que tienen cotizaciones activas
            $query->whereIn('clientes.id', $subquery);
        } elseif ($operador === '=' && !$existeCotizacion) {
            // Clientes que NO tienen cotizaciones activas
            $query->whereNotIn('clientes.id', $subquery);
        }
        
        return $query;
    }
    
    /**
     * Filtra por usuario asignado a la cotización
     */
    protected function filtrarPorAsignacion(Builder $query, string $operador, $valor): Builder
    {
        if (empty($valor)) {
            return $query;
        }
        
        $clientesIds = \DB::table('oportunidades')
            ->join('cotizaciones', 'oportunidades.id', '=', 'cotizaciones.oportunidad_id')
            ->where('cotizaciones.estado', 'active')
            ->where('cotizaciones.vendedor_id', $operador, $valor)
            ->select('oportunidades.cliente_id')
            ->distinct()
            ->pluck('cliente_id')
            ->toArray();
            
        return $query->whereIn('id', $clientesIds);
    }
    
    /**
     * Filtra por monto de cotización
     */
    protected function filtrarPorMontoCotizacion(Builder $query, string $operador, $valor): Builder
    {
        if (!is_numeric($valor)) {
            return $query;
        }
        
        $clientesIds = \DB::table('oportunidades')
            ->join('cotizaciones', 'oportunidades.id', '=', 'cotizaciones.oportunidad_id')
            ->where('cotizaciones.estado', 'active')
            ->where('cotizaciones.total', $operador, $valor)
            ->select('oportunidades.cliente_id')
            ->distinct()
            ->pluck('cliente_id')
            ->toArray();
            
        return $query->whereIn('id', $clientesIds);
    }
    
    /**
     * Filtra por probabilidad de oportunidad
     */
    protected function filtrarPorProbabilidad(Builder $query, string $operador, $valor): Builder
    {
        if (!is_numeric($valor)) {
            return $query;
        }
        
        $clientesIds = \DB::table('oportunidades')
            ->whereNotIn('etapa_actual', ['won', 'lost'])
            ->where('probabilidad', $operador, $valor)
            ->select('cliente_id')
            ->distinct()
            ->pluck('cliente_id')
            ->toArray();
            
        return $query->whereIn('id', $clientesIds);
    }
    
    /**
     * Filtra por tienda
     */
    protected function filtrarPorTienda(Builder $query, string $operador, $valor): Builder
    {
        if (empty($valor)) {
            return $query;
        }
        
        $clientesIds = \DB::table('oportunidades')
            ->join('cotizaciones', 'oportunidades.id', '=', 'cotizaciones.oportunidad_id')
            ->join('usuarios', 'cotizaciones.vendedor_id', '=', 'usuarios.id')
            ->where('usuarios.tienda_id', $operador, $valor)
            ->select('oportunidades.cliente_id')
            ->distinct()
            ->pluck('cliente_id')
            ->toArray();
            
        return $query->whereIn('id', $clientesIds);
    }
    
    /**
     * Filtra por rol del vendedor
     */
    protected function filtrarPorRolVendedor(Builder $query, string $operador, $valor): Builder
    {
        if (empty($valor)) {
            return $query;
        }
        
        $clientesIds = \DB::table('oportunidades')
            ->join('cotizaciones', 'oportunidades.id', '=', 'cotizaciones.oportunidad_id')
            ->join('usuarios', 'cotizaciones.vendedor_id', '=', 'usuarios.id')
            ->join('usuario_rol', 'usuarios.id', '=', 'usuario_rol.usuario_id')
            ->where('usuario_rol.rol_id', $operador, $valor)
            ->select('oportunidades.cliente_id')
            ->distinct()
            ->pluck('cliente_id')
            ->toArray();
            
        return $query->whereIn('id', $clientesIds);
    }
}
