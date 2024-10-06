<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        "owner_type",
        "owner_id",
        "car_capacity_id",
        "car_body_type",
        "type",
        "hourly",
        "min_hours",
        "hours_for_coming",
        "mkad_price",
        "additional_hour_price",
        "additional_point_price",
        "loading_points",
        "unloading_points"
    ];

    public function owner()
    {
        return $this->morphTo();
    }
    public function carCapacity()
    {
        return $this->belongsTo(CarCapacity::class, 'car_capacity_id');
    }
}
