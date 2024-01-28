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
        "opf_short",
        "opf_full",
        "type",
        "inn",
        "kpp",
        "ogrn",
    ];

    public function contacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(ClientBankAccount::class, 'client_id');
    }
}
