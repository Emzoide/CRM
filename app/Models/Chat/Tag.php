<?php
// app/Models/Chat/Tag.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $fillable = [
        'name',
        'color',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_tags')->withTimestamps();
    }
}