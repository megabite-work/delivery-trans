<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class SuggestController extends Controller
{
    public function getCargoNameSuggest(Request $request)
    {
        $res = Order::where("cargo_name", "ilike", "%{$request['q']}%")
            ->distinct()
            ->limit(25)
            ->get("cargo_name as value", "cargo_name as label")
            ->toArray();
        return response()->json($res);
    }

}
