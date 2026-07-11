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
        $flexGuideStorageUrl = config('media.story_audio_storage_url') ?: $localStorageUrl;
        $image = MediaUrl::resolve($this->image_url, $localStorageUrl);

        // Return both storage locations. Clients can select the source based on
        // can_seek without having to reconstruct deployment-specific URLs.
        $audioReference = $this->getRawOriginal('audio_url') ?: $this->audio_path;
        $localSound = MediaUrl::resolve($audioReference, $localStorageUrl);
        $flexGuideSound = MediaUrl::resolve($audioReference, $flexGuideStorageUrl);

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
            'can_seek' => (bool) $this->can_seek,
            'sound_local' => $localSound,
            'sound_flexguide' => $flexGuideSound,
            'sound' => $this->can_seek ? $flexGuideSound : $localSound,
            'effects' => $effects,
            'meta' => $this->meta ?? [],
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
