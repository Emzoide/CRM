<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = ['dni_ruc','nombre','email','phone','address','occupation','canal_id','fec_nac'];

    public function canal()
    {
        return $this->belongsTo(CanalContacto::class, 'canal_id');
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidad::class, 'cliente_id');
    }
}
