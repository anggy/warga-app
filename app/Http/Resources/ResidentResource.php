<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResource extends JsonResource
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
            'name' => $this->name,
            'nik' => $this->nik, // Ensure this is safe to expose or protect it
            'phone' => $this->phone,
            'email' => $this->email,
            'houses' => $this->houses->map(function ($house) {
                return [
                    'id' => $house->id,
                    'block' => $house->block,
                    'number' => $house->number,
                    'role' => $house->pivot->role ?? null, // Owner/Occupant
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
