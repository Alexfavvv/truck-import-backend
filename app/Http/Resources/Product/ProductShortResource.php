<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductShortResource extends JsonResource
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
            'sku' => $this->sku,
            'title' => $this->title,
            'brand_slug' => $this->brand?->slug,
            'brand_name' => $this->brand?->title,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
        ];
    }
}
