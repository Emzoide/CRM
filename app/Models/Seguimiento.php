<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'cliente_id',
        'oportunidad_id',
        'cotizacion_id',
        'usuario_id',
        'contacto_en',
        'resultado',
        'etapa_actual',
        'comentario',
        'proxima_accion'
    ];

    protected $casts = [
        'contacto_en' => 'datetime',
        'resultado' => 'string',
        'etapa_actual' => 'string',
        'created_at' => 'datetime'
    ];

    public static $resultados = [
        'called' => 'Llamada',
        'emailed' => 'Correo',
        'whatsapp' => 'WhatsApp',
        'visited' => 'Visita'
    ];

    public static $etapas = [
        'new' => 'Nuevo',
        'quote_sent' => 'Cotización Enviada',
        'negotiation' => 'En Negociación',
        'won' => 'Venta Cerrada',
        'lost' => 'Venta Perdida'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function oportunidad()
    {
        return $this->belongsTo(Oportunidad::class);
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }
}
