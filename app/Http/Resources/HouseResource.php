<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseResource extends JsonResource
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
            'block' => $this->block,
            'number' => $this->number,
            'address' => $this->address,
            'status' => $this->status,
             // Calculate label based on status if needed, or let frontend handle it
            'status_label' => $this->status === 'occupied' ? 'Terbangun' : 'Kavling',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
