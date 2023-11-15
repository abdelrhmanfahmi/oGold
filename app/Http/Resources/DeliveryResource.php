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
            'total_price' => $this->total_price ?? null,
            'order' => OrderResource::make($this->whenLoaded('order'))
        ];
    }
}
