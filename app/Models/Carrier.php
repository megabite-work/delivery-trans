<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        "name_short",
        "name_full",
        "type",
        "inn",
        "kpp",
        "ogrn",
        "ogrnip_date",
        "vat",
        "is_resident",
        "is_active"
    ];

    public function cars()
    {
        return $this->hasMany(Car::class, "carrier_id");
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class, "carrier_id");
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'owner');
    }

    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'owner');
    }

    public function registries()
    {
        return $this->hasMany(CarrierRegistry::class, 'carrier_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'carrier_id');
    }
}
