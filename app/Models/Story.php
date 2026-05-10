<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'music_id',
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

    protected $appends = [
        'has_image',
        'has_audio',
        'publish_state',
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

    public function music()
    {
        return $this->belongsTo(Music::class);
    }

    public function getHasImageAttribute(): bool
    {
        return !empty($this->image_url);
    }

    public function getHasAudioAttribute(): bool
    {
        return !empty($this->audio_url) || !empty($this->audio_path);
    }

    public function getPublishStateAttribute(): string
    {
        return ($this->has_image && $this->has_audio) ? 'objavljeno' : 'draft';
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

    public function getImageSizeBytesAttribute(): ?int
    {
        $path = $this->resolvePublicDiskPath($this->image_url);

        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->size($path);
    }

    public function getImageSizeLabelAttribute(): ?string
    {
        $bytes = $this->image_size_bytes;

        if ($bytes === null) {
            return null;
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $size >= 10 ? 0 : 1) . ' ' . $units[$unitIndex];
    }

    private function resolvePublicDiskPath(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return null;
        }

        $normalizedPath = '/' . ltrim($path, '/');

        if (!Str::startsWith($normalizedPath, '/storage/')) {
            return null;
        }

        $relativePath = ltrim(Str::after($normalizedPath, '/storage/'), '/');

        return $relativePath !== '' ? $relativePath : null;
    }
}
