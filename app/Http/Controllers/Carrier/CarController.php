<?php

namespace App\Http\Controllers\Carrier;

use App\Enums\CarType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
            'sts_number' => 'nullable|string',
            'sts_date' => 'nullable|date',
        ]);
        if (array_key_exists('sts_date', $data)){
            $data['sts_date'] = Date::parse($data['sts_date'])->timezone("Europe/Moscow");
        }
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
            'sts_number' => 'nullable|string',
            'sts_date' => 'nullable|date',
        ]);
        if (array_key_exists('sts_date', $data)){
            $data['sts_date'] = Date::parse($data['sts_date'])->timezone("Europe/Moscow");
        }
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

    public function getCarsByCarrierId(Request $request)
    {
        $cars = DB::table("cars")
            ->select("id", "name", "body_type", "plate_number", "type", "tonnage", "volume", "loading_rear", "loading_lateral", "loading_upper", "pallets_count")
            ->where("carrier_id", $request['carrier_id'])
            ->get();

        return response()->json($cars);
    }
}
