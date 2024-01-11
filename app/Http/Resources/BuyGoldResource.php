<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyGoldResource extends JsonResource
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
            'volume' => $this->volume ?? null,
            'symbol' => $this->symbol ?? null,
            'buy_price' => $this->buy_price ?? null,
            'price_usd' => $this->when($this->buy_price , $this->volume * $this->buy_price , null),
            'created_at' => $this->created_at ?? null
        ];
    }
}
