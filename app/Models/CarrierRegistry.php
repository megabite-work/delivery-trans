<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        "date",
        "carrier_id",
        "carrier_sum",
        "carrier_paid",
        "vat",
    ];

    protected $table = "carrier_registries";

    protected $appends = ["is_paid"];

    public function orders()
    {
        return $this->hasMany(Order::class, 'carrier_registry_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, "carrier_id");
    }

    public function getIsPaidAttribute()
    {
        return $this->carrier_sum <= $this->carrier_paid;
    }

}
