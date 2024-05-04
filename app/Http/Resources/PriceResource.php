<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "car_capacity_id" => $this->car_capacity_id,
            "car_capacity" => new DTApiResource($this->carCapacity),
            "car_body_type" => $this->car_body_type,
            "type" => $this->type,
            "hourly" => $this->hourly,
            "min_hours" => $this->min_hours,
            "hours_for_coming" => $this->hours_for_coming,
            "mkad_price" => $this->mkad_price,
        ];
    }
}
