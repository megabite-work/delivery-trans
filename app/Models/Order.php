<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "cargo_name",
        "cargo_weight",
        "cargo_temp",
        "cargo_in_pallets",
        "cargo_pallets_count",
        "car_capacity_id",
        "vehicle_body_type",
        "vehicle_loading_rear",
        "vehicle_loading_lateral",
        "vehicle_loading_upper",
        "client_id",
        "client_vat",
        "carrier_id",
        "carrier_vat",
        "carrier_driver_id",
        "carrier_car_id",
        "carrier_trailer_id",
        "carrier_odometer_start",
        "carrier_odometer_end",
        "client_tariff_hourly",
        "client_tariff_min_hours",
        "client_tariff_hours_for_coming",
        "client_tariff_mkad_rate",
        "client_tariff_mkad_price",
        "carrier_tariff_hourly",
        "carrier_tariff_min_hours",
        "carrier_tariff_hours_for_coming",
        "carrier_tariff_mkad_rate",
        "carrier_tariff_mkad_price",
        "client_expenses",
        "client_discounts",
        "carrier_expenses",
        "carrier_fines",
        "from_locations",
        "to_locations",
        "additional_service",
        "client_sum",
        "client_sum_calculated",
        "client_sum_author",
        "carrier_sum",
        "carrier_sum_calculated",
        "carrier_sum_author",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, "carrier_id");
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, "carrier_driver_id");
    }

    public function car()
    {
        return $this->belongsTo(Car::class, "carrier_car_id");
    }

    public function trailer()
    {
        return $this->belongsTo(Car::class, "carrier_trailer_id");
    }

    public function carCapacity()
    {
        return $this->belongsTo(CarCapacity::class, "car_capacity_id");
    }
}
