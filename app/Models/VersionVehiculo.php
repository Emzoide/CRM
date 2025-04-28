<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionVehiculo extends Model
{
    protected $table = 'versiones_vehiculo';
    public $timestamps = false;
    protected $fillable = [
        'modelo_id',
        'nombre',
        'anio'
    ];

    protected $with = ['modelo.marca'];

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    public function detalleCotizacion()
    {
        return $this->hasMany(DetalleCotizacion::class, 'version_vehiculo_id');
    }
}
