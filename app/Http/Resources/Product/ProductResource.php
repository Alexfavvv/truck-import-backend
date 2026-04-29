<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Characteristic\CharacteristicResource;
use App\Http\Resources\Metatag\MetatagResource;
use App\Http\Resources\TruckManufacturer\TruckManufacturerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'brand_id' => $this->brand_id,
            'brand_slug' => $this->brand?->slug,
            'brand_name' => $this->brand?->title,
            'part_type_id' => $this->part_type_id,
            'sku' => $this->sku,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'delivery' => $this->delivery,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
            'truck_manufacturers' => TruckManufacturerResource::collection($this->whenLoaded('truck_manufacturers')),
            'metatags' => MetatagResource::make($this->whenLoaded('metatags')),
            'characteristics' => CharacteristicResource::collection($this->whenLoaded('characteristics')),
        ];
    }
}
