<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'direccion'
    ];

    /**
     * Obtiene la sucursal a la que pertenece esta tienda
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Obtiene los usuarios asociados a esta tienda
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }
}
