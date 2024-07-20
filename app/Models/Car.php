<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        "carrier_id",
        "type",
        "plate_number",
        "name",
        "tonnage",
        "volume",
        "pallets_count",
        "body_type",
        "loading_rear",
        "loading_lateral",
        "loading_upper",
        "sts_number",
        "sts_date"
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, "carrier_id");
    }
}
