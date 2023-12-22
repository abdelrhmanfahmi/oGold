<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
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
            'bank_name' => $this->bank_name ?? null,
            'bank_address' => $this->bank_address ?? null,
            'Iban' => $this->Iban ?? null,
            'bank_account_name' => $this->bank_account_name ?? null,
            'client' => UserResource::make($this->whenLoaded('client')),
        ];
    }
}
