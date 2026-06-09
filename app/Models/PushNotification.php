<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    use HasFactory;

    public const DEFAULT_BODY = 'Brzo, novih 5 pjesama je stiglo.';
    public const DEFAULT_DESCRIPTION = 'Laku noć i lijepi snovi.fm';

    protected $fillable = [
        'body',
        'description',
        'data',
        'recipient_count',
        'success_count',
        'failure_count',
        'response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'response' => 'array',
            'recipient_count' => 'integer',
            'success_count' => 'integer',
            'failure_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }
}
