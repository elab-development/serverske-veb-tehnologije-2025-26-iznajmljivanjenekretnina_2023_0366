<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryResource extends JsonResource
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
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'message' => $this->message,
            'phone' => $this->phone,
            'preferred_date' => $this->preferred_date,
            'preferred_time' => $this->preferred_time,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'user' => new UserResource($this->whenLoaded('user')),
            'property' => new PropertyResource($this->whenLoaded('property')),
        ];
    }
}
