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
            'id' => $this->id,
            'name' => $this->name ?? null,
            'gram' => $this->gram ?? null,
            'image' => $this->image ?? null,
            // 'orders' => OrderResource::collection($this->whenLoaded('orders'))
        ];
    }
}
