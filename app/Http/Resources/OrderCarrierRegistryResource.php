<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class  OrderCarrierRegistryResource extends JsonResource
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
            'date' => $this->date,
            'is_paid' => $this->is_paid,
            'carrier_sum' => $this->carrier_sum,
            'carrier_paid' => $this->carrier_paid,
            'vat' => $this->vat,
            'orders' => CarrierRegistryOrderResource::collection($this->orders),
        ];
    }
}
