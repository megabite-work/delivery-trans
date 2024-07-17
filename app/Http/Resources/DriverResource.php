<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            "carrier_id" => $this->carrier_id,
            "surname" => $this->surname,
            "name" => $this->name,
            "patronymic" => $this->patronymic,
            "birthday" => $this->birthday,
            "inn" => $this->inn,
            "citizenship" => $this->citizenship,
            "passport_number" => $this->passport_number,
            "passport_issuer" => $this->passport_issuer,
            "passport_issuer_code" => $this->passport_issuer_code,
            "passport_issue_date" => $this->passport_issue_date,
            "registration_address" => $this->registration_address,
            "phone" => $this->phone,
            "email" => $this->email,
            "license_number" => $this->license_number,
            "license_expiration" => $this->license_expiration,
            "is_active" => $this->is_active,
        ];
    }
}
