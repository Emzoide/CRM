<?php
// app/Models/Chat/Conversation.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversations';
    protected $fillable = [
        'contact_id',
        'subject',
        'status',
        'last_message_at',
    ];
    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function participants()
    {
        return $this->belongsToMany(
            \App\Models\Usuario::class,
            'conversation_participants',
            'conversation_id',
            'user_id'
        )->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('timestamp');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest('timestamp');
    }
}
