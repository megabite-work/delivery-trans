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

    public function index() {
        return response()->json(Tonnage::all());
    }

    public function store(Request $request) {
        $data = $request->validate([
            'tonnage' => 'required|integer',
        ]);

        $t = Tonnage::create($data);
        return response()->json($t, 201);
    }

    public function destroy(Tonnage $tonnage) {
        $tonnage->delete();
        return response()->noContent();
    }
}
