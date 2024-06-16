<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysInfo extends Controller
{
    public function getInfo()
    {
        $res = DB::select(DB::raw("select version()"));
        return response()->json([
            "R1" => $res,
            "CFG" => config()->all(),
        ]);
    }
}
