<?php

namespace App\Http\Resources\Brand;

use App\Http\Resources\Metatag\MetatagResource;
use App\Http\Resources\PartType\PartTypeResource;
use App\Http\Resources\TruckModel\TruckModelShortResource;
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
            'slug' => $this->slug,
            'name' => $this->title,
            'description' => $this->description,
            ...$this->content,
            'image_url' => $this->image_url,
            'part_types' => PartTypeResource::collection($this->whenLoaded('partTypes')),
            'truck_models' => TruckModelShortResource::collection($this->whenLoaded('truckModels')),
            ...MetatagResource::make($this->metatags),
        ];
    }
}
