<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Order extends Model
{
    use HasFactory;

    protected $appends = ["margin_sum", "margin_percent", "started_at", "status_manager", "status_logist"];

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
        "registry_id",
        "created_by",
        "updated_by",
        "ended_at",
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

    public function statuses()
    {
        return $this->hasMany(OrderStatus::class, "order_id");
    }

    public function registry()
    {
        return $this->belongsTo(Registry::class, "registry_id");
    }

    public function getMarginSumAttribute()
    {
        $res = $this->client_sum - $this->carrier_sum;

        if ($this->client_vat === 1 && $this->carrier_vat === 0) {
            $res = ($this->client_sum / 1.2) - $this->carrier_sum;
        } elseif ($this->client_vat === 1 && $this->carrier_vat === 2) {
            $res = ($this->client_sum / 1.2) - ($this->carrier_sum * 1.12);
        } elseif ($this->client_vat === 0 && $this->carrier_vat === 1) {
            $res = ($this->client_sum * 1.1) - $this->carrier_sum;
        } elseif ($this->client_vat === 2 && $this->carrier_vat === 1) {
            $res = ($this->client_sum * 1.1) - $this->carrier_sum;
        }

        return $res;
    }

    public function getMarginPercentAttribute()
    {
        if ($this->client_sum == 0 || $this->getMarginSumAttribute() == 0 || $this->client_sum == null) {
            return 0;
        }
        return  $this->getMarginSumAttribute() / $this->client_sum;
    }

    public function getStartedAtAttribute()
    {
        $mt = null;
        $locations = json_decode($this->from_locations) ?? [];
        foreach ($locations as $from) {
            if (property_exists($from, 'arrive_date') && property_exists($from, 'arrive_time')) {
                $d = Date::parse($from->arrive_date, null);
                $d->second = 0;
                $d->hour = 0;
                $d->minute = 0;

                if (is_array($from->arrive_time) && count($from->arrive_time) > 0) {
                    $t = Date::parse($from->arrive_time[0], null);
                    $d->hour = $t->hour;
                    $d->minute = $t->minute;
                }

                if ($mt === null || $d->lessThan($mt)) {
                    $mt = $d;
                }
            }
        }
        return $mt;
    }

    public function getStatusManagerAttribute()
    {
        return $this->statuses->where("type", "MANAGER")->sortByDesc("created_at")->first();
    }
    public function getStatusLogistAttribute()
    {
        return $this->statuses->where("type", "LOGIST")->sortByDesc("created_at")->first();
    }

}
