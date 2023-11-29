<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries'))
        ];
    }
}
