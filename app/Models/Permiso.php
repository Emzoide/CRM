<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'grupo'
    ];

    // Relación con roles
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso');
    }
}
