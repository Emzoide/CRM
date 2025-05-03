<?php
// app/Models/Chat/Contact.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $fillable = [
        'wa_id',
        'name',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'contact_tags')->withTimestamps();
    }
}
