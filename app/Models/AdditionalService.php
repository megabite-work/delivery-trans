<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalService extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "owner_type",
        "owner_id",
        "name",
        "price",
        "carrier_price",
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}
