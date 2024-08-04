<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class CarrierRegistryResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => Date::parse($this->date)->timezone("Europe/Moscow")->format('Y-m-d'),
            'is_paid' => $this->is_paid,
            'carrier_id' => $this->client_id,
            'carrier_sum' => $this->client_sum,
            'carrier_paid' => $this->client_paid,
            'vat' => $this->vat,
            'orders' => ClientRegistryOrderResource::collection($this->orders),
        ];
    }
}
