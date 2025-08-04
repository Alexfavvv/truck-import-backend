<?php

namespace App\Http\Resources\TruckModel;
use App\Http\Resources\Metatag\MetatagResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TruckModelResource extends JsonResource
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
            'brand_id' =>$this->brand_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'metatags' => MetatagResource::make($this->metatags),
        ];
    }
}
