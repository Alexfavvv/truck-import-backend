<?php

namespace App\Http\Resources\TruckModel;
use App\Http\Resources\Metatag\MetatagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TruckModelShortResource extends JsonResource
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
            'title' => $this->title,
            'image_url' => $this->image_url,
        ];
    }
}
