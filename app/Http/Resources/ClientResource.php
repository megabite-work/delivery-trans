<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'name_short' => $this->name_short,
            'name_full' => $this->name_full,
            'opf_short' => $this->opf_short,
            'opf_full' => $this->opf_full,
            'type' => $this->type,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'contacts' => ClientContactResource::collection($this->contacts),
            'bank_accounts' => ClientBankAccountResource::collection($this->bankAccounts)
        ];
    }
}
