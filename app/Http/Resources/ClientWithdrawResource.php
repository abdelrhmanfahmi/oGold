<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientWithdrawResource extends JsonResource
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
            'amount' => $this->amount ?? null,
            'currency' => $this->currency ?? null,
            'status' => $this->status ?? null,
            'bank_details' => BankResource::make($this->whenLoaded('bank_details')),
            'client' => BankResource::make($this->whenLoaded('client'))
        ];
    }
}
