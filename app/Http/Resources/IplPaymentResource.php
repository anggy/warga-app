<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IplPaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'period' => $this->period, // e.g., '2023-01'
            'paid_at' => $this->paid_at,
            'status' => $this->status,
            'payer_name' => $this->payer_name,
            'house' => [
                'id' => $this->house_id,
                'block' => $this->house->block ?? null,
                'number' => $this->house->number ?? null,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
