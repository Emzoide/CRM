<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    protected $table = 'detalle_cotizacion';
    public $timestamps = false;
    protected $fillable = [
        'cotizacion_id',
        'version_vehiculo_id',
        'cantidad',
        'precio_unit'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function versionVehiculo()
    {
        return $this->belongsTo(VersionVehiculo::class, 'version_vehiculo_id')
            ->with(['modelo.marca']);
    }
}
