<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraEtapasOportunidad extends Model
{
    protected $table = 'bitacora_etapas_oportunidad';
    public $timestamps = false;

    protected $fillable = ['oportunidad_id','from_stage','to_stage','movido_por'];

    public function oportunidad()
    {
        return $this->belongsTo(Oportunidad::class, 'oportunidad_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'movido_por');
    }
}
