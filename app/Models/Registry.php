<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registry extends Model
{
    use HasFactory;

    protected $fillable = [
        "date",
        "client_id",
        "client_sum",
        "client_paid",
        "vat",
    ];

    protected $table = "registries";

    protected $appends = ["is_paid"];

    public function orders()
    {
        return $this->hasMany(Order::class, 'registry_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function getIsPaidAttribute()
    {
        return $this->client_sum <= $this->client_paid;
    }

}
