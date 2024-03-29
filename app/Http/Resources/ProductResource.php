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
            'image' => $this->when($this->image,env('APP_URL') .'/uploads/' . $this->image,null),
            'charge' => $this->charge ?? null,
            'type' => $this->type ?? null,
            'quantity' => $this->pivot->quantity ?? null
            // 'is_active' => $this->is_active ?? null
            // 'orders' => OrderResource::collection($this->whenLoaded('orders'))
        ];
    }
}
