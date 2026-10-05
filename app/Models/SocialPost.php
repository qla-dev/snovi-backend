<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    protected $fillable = [
        'topic',
        'caption',
        'slides',
        'sources',
        'scheduled_at',
        'status',
        'share_facebook',
        'share_instagram',
    ];

    protected $casts = [
        'slides' => 'array',
        'sources' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'share_facebook' => 'boolean',
        'share_instagram' => 'boolean',
    ];

    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'caption' => $this->caption,
            'status' => $this->status,
            'scheduledAt' => $this->scheduled_at?->toIso8601ZuluString(),
            'publishedAt' => $this->published_at?->toIso8601ZuluString(),
            'slides' => array_map(fn (string $path) => self::publicUrl($path), $this->slides ?? []),
            'sources' => $this->sources ?? [],
            'shareFacebook' => $this->share_facebook,
            'shareInstagram' => $this->share_instagram,
            'facebook' => ['postId' => $this->fb_post_id, 'error' => $this->fb_error],
            'instagram' => ['mediaId' => $this->ig_media_id, 'error' => $this->ig_error],
        ];
    }

    /** Absolute URL Meta downloads the file from (the same from web requests and the cron). */
    public static function publicUrl(string $path): string
    {
        return rtrim((string) config('services.meta.media_url'), '/').'/'.ltrim($path, '/');
    }
}
