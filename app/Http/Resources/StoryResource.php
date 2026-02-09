<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->image_url;
        if ($image && !Str::startsWith($image, ['http://', 'https://', '//'])) {
            $image = url($image);
        }

        $audio = $this->audio_url;
        if ($audio && !Str::startsWith($audio, ['http://', 'https://', '//'])) {
            $audio = url($audio);
        }

        return [
            'id' => $this->slug,
            'slug' => $this->slug,
            'title' => $this->title,
            'narrator' => $this->narrator,
            'duration' => $this->duration_label,
            'duration_seconds' => $this->duration_seconds,
            'image' => $image,
            'description' => $this->description,
            'category' => $this->category?->slug,
            'category_label' => $this->category?->label,
            'subcategory' => $this->subcategory?->label,
            'subcategory_slug' => $this->subcategory?->slug,
            'is_dummy' => $this->is_dummy,
            'locked' => $this->locked,
            'favorite' => $this->is_favorite,
            'sound' => $audio,
            'effects' => $this->effects ?? [],
            'meta' => $this->meta ?? [],
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
