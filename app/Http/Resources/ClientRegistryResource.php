<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class ClientRegistryResource extends JsonResource
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
            'client_id' => $this->client_id,
            'client_sum' => $this->client_sum,
            'client_paid' => $this->client_paid,
            'vat' => $this->vat,
            'orders' => ClientRegistryOrderResource::collection($this->orders),
        ];
    }
}
