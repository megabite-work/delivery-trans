<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarrierRegistryOrderResource extends JsonResource
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
            'created_at' => $this->created_at,
            'carrier_sum' => $this->carrier_sum,
            'carrier_vat' => $this->carrier_vat,
            'status_manager' => $this->status_manager,
            'status_logist' => $this->status_logist,
            'carrier_registry_id' => $this->carrier_registry_id,
            'carrier_id' => $this->carrier_id,
        ];
    }
}
