<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'client' => UserResource::make($this->whenLoaded('client')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries'))
        ];
    }
}