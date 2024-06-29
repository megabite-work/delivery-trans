<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientRegistryOrderResource extends JsonResource
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
            'client_sum' => $this->client_sum,
            'client_vat' => $this->client_vat,
            'status_manager' => $this->status_manager,
            'status_logist' => $this->status_logist,
            'registry_id' => $this->registry_id,
            'client_id' => $this->client_id,
        ];
    }
}
