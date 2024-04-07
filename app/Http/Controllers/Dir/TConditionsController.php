<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\TCondition;
use Illuminate\Http\Request;

class TConditionsController extends Controller
{
    public function index() {
        return response()->json(TCondition::all());
    }
}
