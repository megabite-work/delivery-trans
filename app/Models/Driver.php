<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        "carrier_id",
        "surname",
        "name",
        "patronymic",
        "inn",
        "birthday",
        "citizenship",
        "passport_number",
        "passport_issuer",
        "passport_issuer_code",
        "passport_issue_date",
        "registration_address",
        "phone",
        "email",
        "license_number",
        "license_expiration",
        "is_active",
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, "carrier_id");
    }
}
