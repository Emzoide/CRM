<?php
// app/Models/Chat/Template.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';
    protected $fillable = [
        'name',
        'content',
        'approved',
        'created_by',
    ];
    protected $casts = [
        'approved' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'created_by');
    }
}