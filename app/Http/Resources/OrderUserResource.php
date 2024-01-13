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
        $buyPrice = getBuyPrice();
        if(is_object($buyPrice)){
            return response()->json(['message' => 'Authentication Error !']);
        }else{
            return [
                'id' => $this->id,
                'address_book' => AddressBookResource::make($this->whenLoaded('address_book')),
                'products' => ProductResource::collection($this->whenLoaded('products')),
                'status' => $this->status ?? null,
                'total_gram' => $this->total ?? null,
                'total_charges' => $this->total_charges ?? null,
                'total_amount' => (int) ceil($this->total * $buyPrice[0]->ask),
                'created_at' => $this->created_at ?? null
            ];
        }

    }
}
