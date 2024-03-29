<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'volume' => $this->volume,
            'total_price' => $this->total_price,
            'sender' => UserResource::make($this->whenLoaded('sender')),
            'recieved' => UserResource::make($this->whenLoaded('recieved')),
            'commision' => $this->commision,
            'created_at' => $this->created_at,
        ];
    }
}
