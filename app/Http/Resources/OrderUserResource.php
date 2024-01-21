<?php

namespace App\Http\Resources;

use App\Models\Setting;
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
        $delivery_fees = Setting::where('key' , 'shipping_fees')->value('value');
        return [
            'id' => $this->id,
            'address_book' => AddressBookResource::make($this->whenLoaded('address_book')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'status' => $this->status ?? null,
            'total_gram' => $this->total ?? null,
            'total_charges' => $this->total_charges ?? null,
            'total_amount' => (int) ceil($this->total * $this->buy_price),
            'delivery_fees' => (int) $delivery_fees ?? null,
            'created_at' => $this->created_at ?? null
        ];
    }
}
