<?php
// app/Models/Chat/Message.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = [
        'conversation_id',
        'from_me',
        'message_id',
        'message_type',
        'content',
        'timestamp',
        'client_message_id',
    ];
    protected $casts = [
        'from_me'   => 'boolean',
        'timestamp' => 'datetime:Y-m-d\TH:i:sP',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function media()
    {
        return $this->hasMany(MessageMedia::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(MessageStatusHistory::class);
    }
}
