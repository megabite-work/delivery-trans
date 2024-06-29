<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientRegistryResource extends JsonResource
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
            'client_sum' => $this->client_sum,
            'client_paid' => $this->client_paid,
            'vat' => $this->vat,
            'orders' => ClientRegistryOrderResource::collection($this->orders),
        ];
    }
}
