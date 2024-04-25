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
}
