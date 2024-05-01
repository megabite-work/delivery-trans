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
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}
