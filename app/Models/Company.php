<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name_short",
        "name_full",
        "inn",
        "kpp",
        "ogrn",
        "bik",
        "bank_name",
        "payment_city",
        "account_correspondent",
        "account_payment",
        "sign_position",
        "sign_name",
    ];

}
