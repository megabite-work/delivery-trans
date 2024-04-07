<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TCondition extends Model
{
    use HasFactory;

    protected $table = 'tconditions';
    public $timestamps = false;
    protected $fillable = ['min', 'max'];
}
