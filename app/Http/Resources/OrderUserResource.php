<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderUserResource extends JsonResource
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
            'address_book' => AddressBookResource::make($this->whenLoaded('address_book')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'status' => $this->status ?? null,
            'total_gram' => $this->total ?? null,
            'total_charges' => $this->total_charges ?? null,
            'total_amount' => (int) ceil($this->total * $this->buy_price),
            'created_at' => $this->created_at ?? null
        ];
    }
}
