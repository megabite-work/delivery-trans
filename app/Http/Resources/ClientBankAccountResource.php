<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientBankAccountResource extends JsonResource
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
            'bik' => $this->bik,
            'bank_name' => $this->bank_name,
            'bank_name_payment' => $this->bank_name_payment,
            'payment_city' => $this->payment_city,
            'account_correspondent' => $this->account_correspondent,
            'account_payment' => $this->account_payment,
        ];
    }
}
