<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $localStorageUrl = $request->getSchemeAndHttpHost().'/storage';
        $audioStorageUrl = config('media.story_audio_storage_url') ?: $localStorageUrl;
        $file = MediaUrl::resolve(
            $this->file,
            $audioStorageUrl,
        );

        return [
            'id' => $this->id,
            'name' => $this->name,
            'file' => $file,
        ];
    }
}
