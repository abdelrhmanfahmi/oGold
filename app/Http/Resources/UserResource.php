<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name ?? null,
            'surname' => $this->surname ?? null,
            'email' => $this->email ?? null,
            'dateOfBirth' => $this->dateOfBirth ?? null,
            'phone' => $this->phone ?? null,
            'country' => $this->country ?? null,
            'state' => $this->state ?? null,
            'city' => $this->city ?? null,
            'address' => $this->address ?? null,
            'type' => $this->type ?? null,
        ];
    }
}
