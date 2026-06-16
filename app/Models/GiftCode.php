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
        'used',
        'used_date',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'used_date' => 'datetime',
        ];
    }
}
