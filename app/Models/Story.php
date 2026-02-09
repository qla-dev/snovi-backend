<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'narrator',
        'duration_label',
        'duration_seconds',
        'image_url',
        'description',
        'category_id',
        'subcategory_id',
        'is_dummy',
        'locked',
        'is_favorite',
        'audio_url',
        'audio_path',
        'effects',
        'meta',
        'published_at',
    ];

    protected $casts = [
        'effects' => 'array',
        'meta' => 'array',
        'is_dummy' => 'boolean',
        'locked' => 'boolean',
        'is_favorite' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function getAudioUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->audio_path) {
            return Storage::url($this->audio_path);
        }

        return null;
    }
}
