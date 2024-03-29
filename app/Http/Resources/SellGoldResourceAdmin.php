<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellGoldResourceAdmin extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'client' => UserResource::make($this->whenLoaded('client')),
            'volume' => $this->volume ?? null,
            'symbol' => $this->symbol ?? null,
            'sell_price' => $this->sell_price ?? null,
            'price_usd' => $this->when($this->sell_price , $this->volume * $this->sell_price , null),
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
        ];
    }
}
