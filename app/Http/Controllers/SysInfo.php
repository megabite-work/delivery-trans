<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SysInfo extends Controller
{
    public function getInfo()
    {
        return response()->json(config()->all());
    }
}
