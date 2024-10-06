<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Order extends Model
{
    use HasFactory;

    protected $appends = [
        "margin_sum",
        "margin_percent",
        "started_at",
        "status_manager",
        "status_logist",
        "hours",
        "time_start",
        "time_end",
    ];

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
        "client_tariff_additional_hour_price",
        "client_tariff_additional_point_price",
        "client_tariff_loading_points",
        "client_tariff_unloading_points",
        "carrier_tariff_hourly",
        "carrier_tariff_min_hours",
        "carrier_tariff_hours_for_coming",
        "carrier_tariff_mkad_rate",
        "carrier_tariff_mkad_price",
        "carrier_tariff_additional_hour_price",
        "carrier_tariff_additional_point_price",
        "carrier_tariff_loading_points",
        "carrier_tariff_unloading_points",
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
        "carrier_registry_id",
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

    public function carrierRegistry()
    {
        return $this->belongsTo(Registry::class, "carrier_registry_id");
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

    public function getHoursAttribute()
    {
        $hh = 0;

        $res = [
            "client" => 0,
            "carrier" => 0,
        ];

        $fromLocations = $this->from_locations ? json_decode($this->from_locations) : [];
        $toLocations = $this->to_locations ? json_decode($this->to_locations) : [];
        if (is_array($fromLocations) && (is_array($toLocations) || $this->ended_at)) {
            $mt = null;
            $gt = null;
            foreach ($fromLocations as $from) {
                if (property_exists($from, 'arrive_date') && property_exists($from, 'arrive_time') && is_array($from->arrive_time) && count($from->arrive_time) > 0) {
                    $d = Date::parse($from->arrive_date)->timezone("Europe/Moscow");
                    $t = Date::parse($from->arrive_time[0])->timezone("Europe/Moscow");
                    $d->hour = $t->hour;
                    $d->minute = $t->minute;
                    $d->second = 0;
                    if ($mt === null || $d->lessThan($mt)) {
                        $mt = $d;
                    }
                }
            }
            if($this->ended_at && $this->ended_at != null) {
                $gt = Date::parse($this->ended_at)->timezone("Europe/Moscow");
            } else {
                foreach ($toLocations as $to) {
                    if (property_exists($to, 'arrive_date') && property_exists($to, 'arrive_time') && is_array($to->arrive_time) && count($to->arrive_time) > 0) {
                        $d = Date::parse($to->arrive_date)->timezone("Europe/Moscow");
                        $t = Date::parse($to->arrive_time[0])->timezone("Europe/Moscow");
                        $d->hour = $t->hour;
                        $d->minute = $t->minute;
                        $d->second = 0;
                        if ($gt === null || $d->greaterThan($gt)) {
                            $gt = $d;
                        }
                    }
                }
            }
            if($mt !== null && $gt !== null) {
                $hh = $mt->diffInHours($gt);
            }
            $res["client"] = max($hh, $this->client_tariff_min_hours) + ($this->client_tariff_hours_for_coming ? $this->client_tariff_hours_for_coming : 0);
            $res["carrier"] = max($hh, $this->carrier_tariff_min_hours) + ($this->carrier_tariff_hours_for_coming ? $this->carrier_tariff_hours_for_coming : 0);
        }
        return $res;
    }

    public function getTimeStartAttribute()
    {
        return $this->getTimes()["start"];
    }

    public function getTimeEndAttribute()
    {
        return $this->getTimes()["end"];
    }

    private function getTimes()
    {
        $res = [
            "start" => "",
            "end" => "",
        ];
        $fromLocations = $this->from_locations ? json_decode($this->from_locations) : [];
        $toLocations = $this->to_locations ? json_decode($this->to_locations) : [];

        if (is_array($fromLocations)) {
            $mt = null;
            foreach ($fromLocations as $from) {
                if (property_exists($from, 'arrive_date') && property_exists($from, 'arrive_time') && is_array($from->arrive_time) && count($from->arrive_time) > 0) {
                    $d = Date::parse($from->arrive_date)->timezone("Europe/Moscow");
                    $t = Date::parse($from->arrive_time[0])->timezone("Europe/Moscow");
                    $d->hour = $t->hour;
                    $d->minute = $t->minute;
                    $d->second = 0;
                    if ($mt === null || $d->lessThan($mt)) {
                        $mt = $d;
                    }
                }
            }
            $res["start"] = $mt;
        }
        if (is_array($toLocations)) {
            $gt = null;
            foreach ($toLocations as $to) {
                if (property_exists($to, 'arrive_date') && property_exists($to, 'arrive_time') && is_array($to->arrive_time) && count($to->arrive_time) > 0) {
                    $d = Date::parse($to->arrive_date)->timezone("Europe/Moscow");
                    $t = Date::parse($to->arrive_time[0])->timezone("Europe/Moscow");
                    $d->hour = $t->hour;
                    $d->minute = $t->minute;
                    $d->second = 0;
                    if ($gt === null || $d->greaterThan($gt)) {
                        $gt = $d;
                    }
                }
            }
            $res["end"] = $gt;
        }
        if($this->ended_at && $this->ended_at != null) {
            $res["end"] = Date::parse($this->ended_at)->timezone("Europe/Moscow");
        }

        return $res;
    }
}
