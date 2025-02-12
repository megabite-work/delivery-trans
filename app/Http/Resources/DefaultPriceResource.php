<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DefaultPriceResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "is_default" => $this->is_default,
            "prices" => PriceResource::collection($this->prices),
            "additional_services" => AdditionalServiceResource::collection($this->additionalServicesPrices),
        ];
    }
}
