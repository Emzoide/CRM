<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre'
    ];

    /**
     * Obtiene las tiendas asociadas a esta sucursal
     */
    public function tiendas(): HasMany
    {
        return $this->hasMany(Tienda::class);
    }

    /**
     * Obtiene los usuarios asociados a esta sucursal
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }
}
