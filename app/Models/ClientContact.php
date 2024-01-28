<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    use HasFactory;

    protected $fillable = [
        "client_id",
        "type",
        "value",
        "note",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, "client_id");
    }
}
