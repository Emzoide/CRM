<?php
// app/Models/Chat/MessageMedia.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class MessageMedia extends Model
{
    protected $table = 'message_media';
    protected $fillable = [
        'message_id',
        'media_url',
        'media_type',
        'file_name',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}

