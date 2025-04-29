<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    // 1) Campos "fillables"
    protected $fillable = [
        'login',
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'rol',
        'tienda_id',
        'sucursal_id',
        'activo',
        'last_login',
        'remember_token',
        'full_name',
    ];

    // 2) Campos ocultos al serializar
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // 3) Casts y fechas
    protected $casts = [
        'activo'     => 'boolean',
        'last_login' => 'datetime',
    ];

    // 4) Atributos virtuales para serializar
    protected $appends = [
        'full_name',
    ];

    // Relaciones
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'vendedor_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'usuario_id');
    }

    // 5) Accessor: concatena first_name + last_name
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // 6) Para que Laravel use `password_hash` como password
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
