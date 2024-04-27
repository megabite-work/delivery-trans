<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\CarCapacity;
use Illuminate\Http\Request;

class CarCapacityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(CarCapacity::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "tonnage" => "numeric",
            "volume" => "numeric",
            "pallets_count" => "numeric",
        ]);
        $cap = CarCapacity::create($data);
        return response()->json($cap, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CarCapacity $carCapacity)
    {
        return response()->json($carCapacity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarCapacity $carCapacity)
    {
        $data = $request->validate([
            "tonnage" => "numeric",
            "volume" => "numeric",
            "pallets_count" => "numeric",
        ]);
        $carCapacity->update($data);
        return response()->json($carCapacity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarCapacity $carCapacity)
    {
        $carCapacity->delete();
        return response()->noContent();
    }
}
