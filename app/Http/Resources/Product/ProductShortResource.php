<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Metatag\MetatagResource;
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
            'sku' => $this->sku,
            'title' => $this->title,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
        ];
    }
}
