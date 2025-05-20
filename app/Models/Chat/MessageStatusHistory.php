<?php
// app/Models/Chat/MessageStatusHistory.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class MessageStatusHistory extends Model
{
    protected $table = 'message_status_history';
    public $timestamps = false;
    protected $fillable = [
        'message_id',
        'status',
        'changed_at',
    ];
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
