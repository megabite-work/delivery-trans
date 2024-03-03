<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        "owner_type",
        "owner_id",
        "bik",
        "bank_name",
        "bank_name_payment",
        "payment_city",
        "account_correspondent",
        "account_payment",
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}
