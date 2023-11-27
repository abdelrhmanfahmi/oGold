<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymbolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->symbol,
            'alias' => $this->alias,
            'bid' => $this->bid,
            'ask' => $this->ask,
            'change' => $this->change,
            'high' => $this->high,
            'low' => $this->low,
        ];
    }
}
