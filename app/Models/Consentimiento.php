<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consentimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'email',
        'telefono',
        'acepta_politicas',
        'acepta_comunicaciones',
        'fuente_origen',
        'ip',
        'user_agent',
        'foto_dni_url',
        'firma_digital_url',
        'fecha_aceptacion'
    ];

    protected $casts = [
        'acepta_politicas' => 'boolean',
        'acepta_comunicaciones' => 'boolean',
        'fecha_aceptacion' => 'datetime'
    ];
}
