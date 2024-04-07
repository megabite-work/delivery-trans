<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\Tonnage;
use Illuminate\Http\Request;

class TonnageController extends Controller
{
    public function getTonnage()
    {
        return response()->json(Tonnage::orderBy("tonnage")->get()->pluck("tonnage")->toArray());
    }
}
