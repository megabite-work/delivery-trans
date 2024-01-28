<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        "client_id",
        "bik",
        "bank_name",
        "bank_name_payment",
        "payment_city",
        "account_correspondent",
        "account_payment",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, "client_id");
    }
}
