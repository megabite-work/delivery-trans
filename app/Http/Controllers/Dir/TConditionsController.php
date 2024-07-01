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
    public function store(Request $request) {
        $data = $request->validate([
            'min' => 'required|integer',
            'max' => 'required|integer',
        ]);

        $tcond = TCondition::create($data);
        return response()->json($tcond, 201);
    }

    public function destroy(TCondition $condition) {
        $condition->delete();
        return response()->noContent();
    }
}
