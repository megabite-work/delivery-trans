<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        "owner_type",
        "owner_id",
        "type",
        "value",
        "note",
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}
