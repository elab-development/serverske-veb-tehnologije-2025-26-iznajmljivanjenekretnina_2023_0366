<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'city' => $this->city,
            'address' => $this->address,
            'area' => $this->area,
            'rooms' => $this->rooms,
            'bathrooms' => $this->bathrooms,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'year_built' => $this->year_built,
            'listing_type' => $this->listing_type,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'inquiries' => InquiryResource::collection($this->whenLoaded('inquiries')),
        ];
    }
}
