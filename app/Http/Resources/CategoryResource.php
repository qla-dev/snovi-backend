<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SubcategoryResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'label' => $this->label,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
        ];
    }
}
