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
        "birthday",
        "citizenship",
        "passport_number",
        "passport_issuer",
        "passport_issuer_code",
        "passport_issue_data",
        "registration_address",
        "phone",
        "email",
        "license_number",
        "license_experition",
        "is_active",
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, "carrier_id");
    }
}
