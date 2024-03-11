<?php

namespace App\Http\Controllers\Carrier;

use App\Enums\CarType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function carrierCarsIndex(Request $request)
    {
        return Car::where('carrier_id', $request['carrier_id'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'carrier_id' => 'required|exists:App\Models\Carrier,id',
            'type' => ['required', new Enum(CarType::class)],
            'plate_number' => 'required|string',
            'name' => 'required|string',
            'tonnage' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'pallets_count' => 'nullable|numeric',
            'body_type' => 'nullable|exists:App\Models\CarBodyType,type',
            'loading_rear' => 'nullable|boolean',
            'loading_lateral' => 'nullable|boolean',
            'loading_upper' => 'nullable|boolean',
        ]);
        $car = Car::create($data);
        return response()->json(new CarResource($car), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        return new CarResource($car);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        $data = $request->validate([
            'type' => ['required', new Enum(CarType::class)],
            'plate_number' => 'required|string',
            'name' => 'required|string',
            'tonnage' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'pallets_count' => 'nullable|numeric',
            'body_type' => 'nullable|exists:App\Models\CarBodyType,type',
            'loading_rear' => 'nullable|boolean',
            'loading_lateral' => 'nullable|boolean',
            'loading_upper' => 'nullable|boolean',
        ]);
        $car->update($data);
        return response()->json(new CarResource($car));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->noContent();
    }
}
