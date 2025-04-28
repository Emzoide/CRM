<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoFinanciero extends Model
{
    protected $table = 'productos_financieros';
    public $timestamps = false;
    protected $fillable = ['banco_id','nombre','tipo'];

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }
}
