<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\CarBodyType;
use Illuminate\Http\Request;

class CarBodyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CarBodyType::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
        ]);
        $carBodyType = CarBodyType::create($data);
        return response()->json($carBodyType, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarBodyType $carBodyType)
    {
        $carBodyType->delete();
        return response()->noContent();
    }
}
