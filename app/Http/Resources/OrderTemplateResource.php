<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class OrderTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vat = ["Без НДС", "C НДС", "Нал"];
        $car_types = ["TRUCK" => "Грузовик", "TRACTOR" => "Тягач", "TRAILER" => "TRAILER", "-" => "-"];
        $loading_types = [];
        if ($this->vehicle_loading_rear) {
            $loading_types[] = 'Задняя';
        }
        if ($this->vehicle_loading_lateral) {
            $loading_types[] = 'Боковая';
        }
        if ($this->vehicle_loading_upper) {
            $loading_types[] = 'Верхняя';
        }
        $kv_reducer = function ($r, $item) {
            $a = $item->k.' – '.$item->v;
            if($r !== "") {
                $a = ", ".$a;
            }
            return $r.$a;
        };
        $kcv_reducer = function ($r, $item) {
            $a = $item->k.' x '.($item->c ?? 1).' – '.$item->v;
            if($r !== "") {
                $a = ", ".$a;
            }
            return $r.$a;
        };

        $address_reducer = function ($r, $item) {
            $a = $item->address;
            if($r !== "") {
                $a = ", ".$a;
            }
            return $r.$a;
        };

        $address_contacts_name_reducer = function ($r, $item) {
            $a = $item->contact_person;
            if($r !== "") {
                $a = ", ".$a;
            }
            return $r.$a;
        };

        $address_contacts_phone_reducer = function ($r, $item) {
            $a = $item->contact_phone;
            if($r !== "") {
                $a = ", ".$a;
            }
            return $r.$a;
        };

        $client_bank = $this->client->bankAccounts->first();
        $carrier_bank = $this->carrier->bankAccounts->first();

        $client_addres_yur = $this->client->contacts->where("type", "ADDRESS")->where("note", "Юридический адрес")->first();
        $client_addres_real = $this->client->contacts->where("type", "ADDRESS")->where("note", "Фактический адрес")->first();
        $client_addres_post = $this->client->contacts->where("type", "ADDRESS")->where("note", "Почтовый адрес")->first();

        $carrier_addres_yur = $this->carrier->contacts->where("type", "ADDRESS")->where("note", "Юридический адрес")->first();
        $carrier_addres_real = $this->carrier->contacts->where("type", "ADDRESS")->where("note", "Фактический адрес")->first();
        $carrier_addres_post = $this->carrier->contacts->where("type", "ADDRESS")->where("note", "Почтовый адрес")->first();

        $bank_string = function ($bank) {
            if ($bank === null) {
                return "";
            }
            return "р/с ".$bank->account_payment.' в '.$bank->bank_name.' '.$bank->payment_city.', к/с '.$bank->account_correspondent.', БИК '.$bank->bik;
        };

        $res = [
            'id' => $this->id,

            'cargo_name' => $this->cargo_name ?? "",
            'cargo_weight' => number_format($this->cargo_weight / 1000, 2),
            'cargo_temp' => $this->cargo_temp ?? "",
            'cargo_pallets' => $this->cargo_pallets_count ?? "",

            'car_capacity' => $this->carCapacity->tonnage.'т. – '.$this->carCapacity->volume.'м³. – '.$this->carCapacity->pallets_count.'п.',
            'car_body_type' => $this->vehicle_body_type ?? "",
            'car_loading' => implode(', ', $loading_types),

            'client_name_short' => $this->client->name_short ?? "",
            'client_name_full' => $this->client->name_full ?? "",
            'client_inn' => $this->client->inn ?? "",
            'client_kpp' => $this->client->kpp ?? "",
            'client_ogrn' => $this->client->ogrn ?? "",
            'client_vat' => $vat[$this->client->vat],

            'client_bank' => $bank_string($client_bank),
            'client_bank_bik' => $client_bank->bik ?? "",
            'client_bank_name' => $client_bank->bank_name ?? "",
            'client_bank_city' => $client_bank->bank_city ?? "",
            'client_bank_correspondent' => $client_bank->account_correspondent ?? "",
            'client_bank_account' => $client_bank->account_payment ?? "",

            'client_address_yur' => $client_addres_yur->value ?? "",
            'client_address_real' => $client_addres_real->value ?? "",
            'client_address_post' => $client_addres_post->value ?? "",

            'carrier_name_short' => $this->carrier->name_short ?? "",
            'carrier_name_full' => $this->carrier->name_full ?? "",
            'carrier_inn' => $this->carrier->inn ?? "",
            'carrier_kpp' => $this->carrier->kpp ?? "",
            'carrier_ogrn' => $this->carrier->ogrn ?? "",
            'carrier_vat' => $vat[$this->carrier->vat],

            'carrier_bank' => $bank_string($carrier_bank),
            'carrier_bank_bik' => $carrier_bank->bik ?? "",
            'carrier_bank_name' => $carrier_bank->bank_name ?? "",
            'carrier_bank_city' => $carrier_bank->bank_city ?? "",
            'carrier_bank_correspondent' => $carrier_bank->account_correspondent ?? "",
            'carrier_bank_account' => $carrier_bank->account_payment ?? "",

            'carrier_address_yur' => $carrier_addres_yur->value ?? "",
            'carrier_address_real' => $carrier_addres_real->value ?? "",
            'carrier_address_post' => $carrier_addres_post->value ?? "",


            'driver_name_full' => implode(' ', [$this->driver->surname, $this->driver->name, $this->driver->patronymic]),
            'driver_name_short' => $this->driver->surname.' '
                .($this->driver->name ? mb_substr($this->driver->name, 0, 1).'.' : '')
                .($this->driver->patronymic ? mb_substr($this->driver->patronymic, 0, 1).'.' : ''),
            'driver_inn' => $this->driver->inn ?? "",
            'driver_passport' => $this->driver->passport_number
                .($this->driver->passport_issuer ? ', выдан '.$this->driver->passport_issuer : '')
                .($this->driver->passport_issue_date ? ', '.Date::parse($this->driver->passport_issue_date)->timezone("Europe/Moscow")->format('d.m.Y') : '')
                .($this->driver->passport_issuer_code ? ', код подразделения '.$this->driver->passport_issuer_code : ''),
            'driver_passport_issuer' => $this->driver->passport_issuer ?? "",
            'driver_passport_issue_date' => Date::parse($this->driver->passport_issue_date)->timezone("Europe/Moscow")->format('d.m.Y'),
            'driver_passport_issuer_code' => $this->driver->passport_issuer_code ?? "",
            'driver_phone' => $this->driver->phone ?? "",
            'driver_email' => $this->driver->email ?? "",
            'driver_license_number' => $this->driver->license_number ?? "",
            'driver_license_expiration' => Date::parse($this->driver->license_expiration)->timezone("Europe/Moscow")->format('d.m.Y'),

            'car_name' => $this->car->name ?? '',
            'car_type' => $car_types[$this->car->type ?? '-'],
            'car_plate' => $this->car->plate_number ?? '',
            'car_sts_number' => $this->car->sts_number ?? '',
            'car_sts_date' => $this->car->sts_date ? Date::parse($this->car->sts_date)->timezone("Europe/Moscow")->format('d.m.Y') : '',
            'car_b_type' => $this->car->body_type ?? '',

            'trailer_name' => $this->trailer->name ?? '',
            'trailer_type' => $car_types[$this->trailer->type ?? '-'],
            'trailer_plate' => $this->trailer->plate_number ?? '',
            'trailer_sts_number' => $this->trailer->sts_number ?? '',
            'trailer_sts_date' => $this->trailer->sts_date ? Date::parse($this->trailer->sts_date)->timezone("Europe/Moscow")->format('d.m.Y') : '',
            'trailer_b_type' => $this->trailer->body_type ?? '',

            'client_tariff_hourly' => $this->client_tariff_hourly ?? "",
            'client_tariff_min_hours' => $this->client_tariff_min_hours ?? "",
            'client_tariff_hours_for_coming' => $this->client_tariff_hours_for_coming ?? "",
            'client_tariff_mkad_rate' => $this->client_tariff_mkad_rate ?? "",
            'client_tariff_mkad_price' => $this->client_tariff_mkad_price ?? "",

            'carrier_tariff_hourly' => $this->carrier_tariff_hourly ?? "",
            'carrier_tariff_min_hours' => $this->carrier_tariff_min_hours ?? "",
            'carrier_tariff_hours_for_coming' => $this->carrier_tariff_hours_for_coming ?? "",
            'carrier_tariff_mkad_rate' => $this->carrier_tariff_mkad_rate ?? "",
            'carrier_tariff_mkad_price' => $this->carrier_tariff_mkad_price ?? "",

            'client_expenses' => array_reduce(json_decode($this->client_expenses ?? '[]'), $kv_reducer, ""),
            'client_discounts' => array_reduce(json_decode($this->client_discounts ?? '[]'), $kv_reducer, ""),

            'carrier_expenses' => array_reduce(json_decode($this->carrier_expenses ?? '[]'), $kv_reducer, ""),
            'carrier_fines' => array_reduce(json_decode($this->carrier_fines ?? '[]'), $kv_reducer, ""),

            'from_location_address' => array_reduce(json_decode($this->from_locations ?? '[]'), $address_reducer, ""),
            'from_location_contact_name' => array_reduce(json_decode($this->from_locations ?? '[]'), $address_contacts_name_reducer, ""),
            'from_location_contact_phone' => array_reduce(json_decode($this->from_locations ?? '[]'), $address_contacts_phone_reducer, ""),

            'to_location_address' => array_reduce(json_decode($this->to_locations ?? '[]'), $address_reducer, ""),
            'to_location_contact_name' => array_reduce(json_decode($this->to_locations ?? '[]'), $address_contacts_name_reducer, ""),
            'to_location_contact_phone' => array_reduce(json_decode($this->to_locations ?? '[]'), $address_contacts_phone_reducer, ""),

            'additional_service' => array_reduce(json_decode($this->additional_service ?? '[]'), $kcv_reducer, ""),

            'client_sum' => $this->client_sum ?? "",
            'carrier_sum' => $this->carrier_sum ?? "",
            'started_at' => $this->started_at ?? "",
            'created_at' => $this->created_at ?? "",
            'updated_at' => $this->updated_at ?? "",

            'ended_at' => $this->ended_at == null ? null : Date::parse($this->ended_at)->timezone("Europe/Moscow"),

            'client_sum_author' => number_format($this->client_sum_author, 2),
            'carrier_sum_author' => number_format($this->carrier_sum_author, 2),

            'margin_sum' => number_format($this->marginSum, 2),
            'margin_percent' => number_format(($this->marginPercent ?? 0) * 100, 2),
            'hours_client' => $this->hours["client"] ?? '',
            'hours_carrier' => $this->hours["carrier"] ?? '',
            "date_start" => Date::parse($this->time_start)->timezone("Europe/Moscow")->format('d.m.Y'),
            "date_end" => Date::parse($this->time_end)->timezone("Europe/Moscow")->format('d.m.Y'),
            "time_start" => Date::parse($this->time_start)->timezone("Europe/Moscow")->format('H:i'),
            "time_end" => Date::parse($this->time_end)->timezone("Europe/Moscow")->format('H:i'),
        ];

        return $res;
    }
}
