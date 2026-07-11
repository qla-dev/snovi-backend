<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localStorageUrl = $request->getSchemeAndHttpHost().'/storage';
        $storyAudioStorageUrl = config('media.story_audio_storage_url') ?: $localStorageUrl;
        $image = MediaUrl::resolve($this->image_url, $localStorageUrl);

        // Only story sound can use the optional remote storage host.
        $audio = $this->audio_url ?: $this->audio_path;
        $audio = MediaUrl::resolve($audio, $storyAudioStorageUrl);

        $musicLevel = (int) ($this->music_level ?? 20);
        $effects = $this->effects ?? [];
        $effects['music'] = (int) round($musicLevel / 10);

        return [
            'id' => $this->slug,
            'slug' => $this->slug,
            'title' => $this->title,
            'narrator' => $this->narrator,
            'duration' => $this->duration_label,
            'duration_seconds' => $this->duration_seconds,
            'image' => $image,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->category?->slug,
            'category_label' => $this->category?->label,
            'subcategory_id' => $this->subcategory_id,
            'subcategory' => $this->subcategory?->label,
            'subcategory_slug' => $this->subcategory?->slug,
            'music_id' => $this->music_id,
            'music_level' => $musicLevel,
            'music' => $this->whenLoaded('music', fn () => new MusicResource($this->music)),
            'is_dummy' => $this->is_dummy,
            'locked' => $this->locked,
            'favorite' => $this->is_favorite,
            'sound' => $audio,
            'effects' => $effects,
            'meta' => $this->meta ?? [],
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
