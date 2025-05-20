<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = ['dni_ruc', 'nombre', 'fec_nac', 'email', 'phone', 'address', 'occupation', 'canal_id'];

    // Estos métodos permiten acceder a datos históricos de las cotizaciones
    // pero priorizan los datos actuales del cliente si existen
    public function getEmailHistoricoAttribute()
    {
        return optional($this->ultimaCotizacionActiva)->email;
    }

    public function getPhoneHistoricoAttribute()
    {
        return optional($this->ultimaCotizacionActiva)->phone;
    }

    public function getAddressHistoricoAttribute()
    {
        return optional($this->ultimaCotizacionActiva)->address;
    }

    public function getOccupationHistoricoAttribute()
    {
        return optional($this->ultimaCotizacionActiva)->occupation;
    }

    // El canal ahora se gestiona a través de la oportunidad
    // Esta relación se mantiene por compatibilidad con el código existente
    public function canal()
    {
        // Creamos una relación directa a CanalContacto
        // Esto devuelve una instancia de belongsTo que es una subclase de Relation
        return $this->belongsTo(CanalContacto::class, 'canal_id');
    }
    
    // Método auxiliar para obtener el nombre del canal
    public function getNombreCanalAttribute()
    {
        // Intentamos obtener desde la relación directa primero
        if ($this->canal && $this->canal->nombre) {
            return $this->canal->nombre;
        }
        
        // Si no existe relación directa, intentamos obtenerlo desde la última oportunidad
        $ultimaOportunidad = $this->oportunidades()->orderBy('created_at', 'desc')->first();
        if ($ultimaOportunidad && $ultimaOportunidad->canalFuente) {
            return $ultimaOportunidad->canalFuente->nombre;
        }
        
        return 'No definido';
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidad::class, 'cliente_id');
    }
    
    // Nueva relación para obtener la última cotización activa como atributo
    public function getUltimaCotizacionActivaAttribute()
    {
        return $this->hasManyThrough(
            Cotizacion::class,
            Oportunidad::class,
            'cliente_id',
            'oportunidad_id'
        )->where('cotizaciones.estado', 'active')
        ->orderBy('cotizaciones.emitida_en', 'desc')
        ->first();
    }
}
