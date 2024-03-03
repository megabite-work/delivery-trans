<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarrierResource extends JsonResource
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
            'name_short' => $this->name_short,
            'name_full' => $this->name_full,
            'type' => $this->type,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'is_resident' => $this->is_resident,
            'is_active' => $this->is_active,
            'contacts' => ContactResource::collection($this->contacts),
            'bank_accounts' => BankAccountResource::collection($this->bankAccounts)
        ];
    }
}
