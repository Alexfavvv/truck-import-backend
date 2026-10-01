<?php

namespace App\Http\Resources\Engine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'type' => $this->type,
            'sku' => $this->sku,
            'price' => $this->price,
            'currency' => $this->currency,
            'availability' => $this->availability,
            'image' => $this->image,
            'description' => $this->description,
            'engine_family' => $this->engine_family,
            'displacement' => $this->displacement,
            'power' => $this->power,
            'fuel_type' => $this->fuel_type,
            'application' => $this->application,
            'condition' => $this->condition,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
