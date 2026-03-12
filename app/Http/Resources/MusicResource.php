<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class MusicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $file = $this->file;
        if ($file && !Str::startsWith($file, ['http://', 'https://', '//'])) {
            $file = $request->getSchemeAndHttpHost() . '/' . ltrim($file, '/');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'file' => $file,
        ];
    }
}
