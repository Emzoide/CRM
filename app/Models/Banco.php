<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    protected $table = 'bancos';
    public $timestamps = false;
    protected $fillable = ['nombre'];

    public function productosFinancieros()
    {
        return $this->hasMany(ProductoFinanciero::class, 'banco_id');
    }
}
