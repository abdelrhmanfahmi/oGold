<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResource extends JsonResource
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
            'name' => $this->name ?? null,
            'uuid' => $this->uuid ?? null,
            'preimum_fees' => $this->preimum_fees ?? null,
            'products' => ProductResource::make($this->whenLoaded('products')),
        ];
    }
}
