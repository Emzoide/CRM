<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanalContacto extends Model
{
    protected $table = 'canales_contacto';
    public $timestamps = false;
    protected $fillable = ['nombre'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'canal_id');
    }

    public function oportunidadesFuente()
    {
        return $this->hasMany(Oportunidad::class, 'canal_fuente_id');
    }
}
