<?php

namespace App\Http\Resources\Brand;

use App\Http\Resources\Metatag\MetatagResource;
use App\Http\Resources\PartType\PartTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'part_types' => PartTypeResource::collection($this->whenLoaded('partTypes')),
            'metatags' => MetatagResource::make($this->metatags),
        ];
    }
}
