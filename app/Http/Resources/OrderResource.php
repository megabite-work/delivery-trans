<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $res = [
            'id' => $this->id,
            'statuses' => $this->statuses,
            'status_manager' => $this->status_manager,
            'status_logist' => $this->status_logist,
            'cargo_name' => $this->cargo_name,
            'cargo_weight' => $this->cargo_weight,
            'cargo_temp' => $this->cargo_temp,
            'cargo_in_pallets' => $this->cargo_in_pallets,
            'cargo_pallets_count' => $this->cargo_pallets_count,
            'car_capacity_id' => $this->car_capacity_id,
            'car_capacity' => $this->carCapacity,
            'vehicle_body_type' => $this->vehicle_body_type,
            'vehicle_loading_rear' => $this->vehicle_loading_rear,
            'vehicle_loading_lateral' => $this->vehicle_loading_lateral,
            'vehicle_loading_upper' => $this->vehicle_loading_upper,
            'client_id' => $this->client_id,
            'client' => $this->client,
            'client_vat' => $this->client_vat,
            'carrier_id' => $this->carrier_id,
            'carrier' => $this->carrier,
            'carrier_vat' => $this->carrier_vat,
            'carrier_driver_id' => $this->carrier_driver_id,
            'carrier_driver' => $this->driver,
            'carrier_car_id' => $this->carrier_car_id,
            'carrier_car' => $this->car,
            'carrier_trailer_id' => $this->carrier_trailer_id,
            'carrier_trailer' => $this->trailer,
            'carrier_odometer_start' => $this->carrier_odometer_start,
            'carrier_odometer_end' => $this->carrier_odometer_end,
            'client_tariff_hourly' => $this->client_tariff_hourly,
            'client_tariff_min_hours' => $this->client_tariff_min_hours,
            'client_tariff_hours_for_coming' => $this->client_tariff_hours_for_coming,
            'client_tariff_mkad_rate' => $this->client_tariff_mkad_rate,
            'client_tariff_mkad_price' => $this->client_tariff_mkad_price,
            'carrier_tariff_hourly' => $this->carrier_tariff_hourly,
            'carrier_tariff_min_hours' => $this->carrier_tariff_min_hours,
            'carrier_tariff_hours_for_coming' => $this->carrier_tariff_hours_for_coming,
            'carrier_tariff_mkad_rate' => $this->carrier_tariff_mkad_rate,
            'carrier_tariff_mkad_price' => $this->carrier_tariff_mkad_price,
            'client_expenses' => json_decode($this->client_expenses),
            'client_discounts' => json_decode($this->client_discounts),
            'carrier_expenses' => json_decode($this->carrier_expenses),
            'carrier_fines' => json_decode($this->carrier_fines),
            'from_locations' => json_decode($this->from_locations),
            'to_locations' => json_decode($this->to_locations),
            'additional_service' => json_decode($this->additional_service),
            'client_sum' => $this->client_sum,
            'carrier_sum' => $this->carrier_sum,
            'started_at' => $this->started_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_sum_calculated' => $this->client_sum_calculated,
            'carrier_sum_calculated' => $this->carrier_sum_calculated,
            'client_sum_author' => $this->client_sum_author,
            'carrier_sum_author' => $this->carrier_sum_author,
            'margin_sum' => $this->marginSum,
            'margin_percent' => $this->marginPercent,
            'registry_id' => $this->registry_id,
            'registry' => OrderRegistryResource::make($this->registry),
        ];

        return array_filter($res, fn($v) => !is_null($v) && $v !== '');
    }
}
