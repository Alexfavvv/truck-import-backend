<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $brandTitle = $product && $product->relationLoaded('brand') && $product->brand ? $product->brand->title : null;
        $skuWithBrand = $brandTitle ? $brandTitle . '-' . $product->sku : $product->sku;

        return [
            'id' => $this->id,
            'sku' => $skuWithBrand,
            'title' => $product->title,
            'price' => $product->price,
            'image_url' => $product->image_url,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}

