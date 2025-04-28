<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cotizacion;
use App\Models\Usuario;
use App\Models\CanalContacto;
use App\Models\DetalleCotizacion;

class Oportunidad extends Model
{
    protected $table = 'oportunidades';
    public $timestamps = false; // solo created_at
    protected $casts = [
        'created_at' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    protected $fillable = [
        'cliente_id',
        'canal_fuente_id',
        'etapa_actual',
        'probabilidad',
        'razon_decision',
        'fecha_cierre',
        'monto_final',
        'motivo_cierre',
        'cerrado_por'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function canalFuente()
    {
        return $this->belongsTo(CanalContacto::class, 'canal_fuente_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'oportunidad_id');
    }

    /**
     * La cotización más reciente (ultimaCotizacion)
     */
    public function ultimaCotizacion()
    {
        return $this->hasOne(Cotizacion::class, 'oportunidad_id')
            ->latestOfMany('emitida_en');
    }
    public function getCotizacionActivaAttribute()
    {
        return $this->cotizacionActiva()->first();
    }
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'oportunidad_id');
    }

    public function bitacora()
    {
        return $this->hasMany(BitacoraEtapasOportunidad::class, 'oportunidad_id');
    }

    public function cerrador()
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }

    public function puedeSerCerrada()
    {
        return !in_array($this->etapa_actual, ['won', 'lost'])
            && $this->seguimientos()->exists();
    }

    public function cotizacionActiva()
    {
        return $this->hasOne(Cotizacion::class, 'oportunidad_id')
            ->where('estado', 'active')
            ->with([
                'vendedor:id,full_name',
                'banco:id,nombre',
                'detalles' => function ($query) {
                    $query->with([
                        'versionVehiculo.modelo.marca'
                    ]);
                }
            ]);
    }
}
