<?php
// app/Models/Chat/WebhookEvent.php
namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $table = 'webhook_events';
    public $timestamps = false;
    protected $fillable = [
        'event_type',
        'payload',
    ];
    protected $casts = [
        'payload' => 'array',
    ];
    protected $dates = ['received_at'];
}
