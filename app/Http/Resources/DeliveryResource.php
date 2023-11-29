<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'address' => $this->address ?? null,
            'payment_type' => $this->payment_type ?? null,
            'status' => $this->status ?? null,
            'is_approved' => $this->is_approved ?? null,
            'total' => $this->total ?? null,
            'order' => OrderResource::make($this->whenLoaded('order'))
        ];
    }
}
