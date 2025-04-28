<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    protected static function booted()
    {
        static::created(function ($cot) {
            // Si ya tiene código (por algún motivo), no lo sobreescribimos
            if (! $cot->codigo || ! str_starts_with($cot->codigo, 'COT-')) {
                $cot->codigo = sprintf(
                    'COT-%04d',      // COT- seguido de 4 dígitos, con ceros a la izquierda
                    $cot->id
                );
                // guardamos sin disparar eventos nuevamente
                $cot->saveQuietly();
            }
        });
    }
    protected $fillable = [
        'oportunidad_id',
        'codigo',
        'emitida_en',
        'vence_en',
        'vendedor_id',
        'total',
        'estado',
        'motivo_rechazo',
        'rechazada_en',
        'rechazada_por',
        'tipo_compra',
        'banco_id',
        'banco_otro',
        'compra_plazos',
        'razon_no_plazos',
        'seguro_vehicular',
        'razon_no_seguro',
        'observacion_call_center'
    ];
    protected $dates = [
        'emitida_en',
        'vence_en',
        'rechazada_en',
        'created_at',
        'updated_at'
    ];
    // Definir los posibles estados de una cotización
    const ESTADOS = [
        'active' => 'Activa',
        'superseded' => 'Reemplazada',
        'client-rejected' => 'Rechazada por cliente',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
        'historical' => 'Histórica'
    ];
    // Relaciones
    public function oportunidad()
    {
        return $this->belongsTo(Oportunidad::class, 'oportunidad_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function rechazador()
    {
        return $this->belongsTo(Usuario::class, 'rechazada_por');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'active');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'approved');
    }

    public function scopeRechazadas($query)
    {
        return $query->whereIn('estado', ['client-rejected', 'rejected']);
    }

    // Métodos
    public function esActiva()
    {
        return $this->estado === 'active';
    }

    public function esAprobada()
    {
        return $this->estado === 'approved';
    }

    public function esRechazada()
    {
        return in_array($this->estado, ['client-rejected', 'rejected']);
    }
}
