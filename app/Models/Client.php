<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
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
    ];

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'owner');
    }

    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'owner');
    }

    public function prices()
    {
        return $this->morphMany(Price::class, 'owner');
    }

    public function additionalServicesPrices()
    {
        return $this->morphMany(AdditionalService::class, 'owner');
    }

    public function registries()
    {
        return $this->hasMany(Registry::class, 'client_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }
}
