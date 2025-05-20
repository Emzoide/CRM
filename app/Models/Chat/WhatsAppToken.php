<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class WhatsAppToken extends Model
{
    protected $table = 'whatsapp_tokens';
    protected $fillable = [
        'token',
        'expires_at',
        'is_active'
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public static function getActiveToken()
    {
        return static::where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
    }

    public static function deactivateAllTokens()
    {
        return static::where('is_active', true)->update(['is_active' => false]);
    }
}
