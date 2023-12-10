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
            'address_book' => AddressBookResource::make($this->whenLoaded('address_book')),
            'client' => UserResource::make($this->whenLoaded('client')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'status' => $this->status ?? null,
            'is_approved' => $this->is_approved ?? null,
            'total' => $this->total ?? null,
        ];
    }
}
