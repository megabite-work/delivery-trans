<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarBodyType extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'type';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'type',
    ];
}
