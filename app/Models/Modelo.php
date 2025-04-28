<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table = 'modelos';
    public $timestamps = false;
    protected $fillable = [
        'marca_id',
        'nombre'
    ];

    protected $with = ['marca'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function versiones()
    {
        return $this->hasMany(VersionVehiculo::class, 'modelo_id');
    }
}
