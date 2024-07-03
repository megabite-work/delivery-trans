<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'vat' => $this->vat,
            'contacts' => ContactResource::collection($this->contacts),
            'bank_accounts' => BankAccountResource::collection($this->bankAccounts),
            'prices' => PriceResource::collection($this->prices),
            "additional_services" => AdditionalServiceResource::collection($this->additionalServicesPrices),
            'orders' => ClientRegistryOrderResource::collection($this->orders),
            'registries' => ClientRegistryResource::collection($this->registries),
        ];
    }
}
