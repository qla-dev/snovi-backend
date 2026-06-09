<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'provider',
        'platform',
        'notification_channel_id',
        'sound',
        'preferences',
        'last_used_at',
        'disabled_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
            'last_used_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }
}
