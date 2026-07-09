<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'email',
        'used',
        'used_date',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'used_date' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
