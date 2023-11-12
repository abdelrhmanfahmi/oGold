<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name ?? null, 
            'price' => $this->price ?? null,
            'image' => $this->image ?? null,
            'orders' => OrderResource::collection($this->whenLoaded('orders'))
        ];
    }
}
