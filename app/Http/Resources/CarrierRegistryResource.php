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
            'carrier_id' => $this->carrier_id,
            'carrier_sum' => $this->carrier_sum,
            'carrier_paid' => $this->carrier_paid,
            'vat' => $this->vat,
            "bill_number" => $this->bill_number,
            "bill_date" => $this->bill_date,
            'orders' => CarrierRegistryOrderResource::collection($this->orders),
        ];
    }
}
