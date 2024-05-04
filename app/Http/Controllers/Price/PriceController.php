<?php

namespace App\Http\Controllers\Price;

use App\Enums\PriceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Models\Client;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PriceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function storeForClient(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:App\Models\Client,id',
            'type' => ['required',new Enum(PriceType::class)],
            'car_body_type' => 'required|exists:App\Models\CarBodyType,type',
            'car_capacity_id' => 'required|exists:App\Models\CarCapacity,id',
            'hourly' => 'required|numeric|min:0',
            'hours_for_coming' => 'required|numeric|min:0',
            'min_hours' => 'required|numeric|min:0',
            'mkad_price' => 'required|numeric|min:0',
        ]);
        $data["owner_type"] = Client::class;
        $data["owner_id"] = $data["client_id"];

        $price = Price::create($data);
        return response()->json(new PriceResource($price), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price)
    {
        return new PriceResource($price);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Price $price)
    {
        $data = $request->validate([
            'type' => ['required',new Enum(PriceType::class)],
            'car_body_type' => 'required|exists:App\Models\CarBodyType,type',
            'car_capacity_id' => 'required|exists:App\Models\CarCapacity,id',
            'hourly' => 'required|numeric|min:0',
            'hours_for_coming' => 'required|numeric|min:0',
            'min_hours' => 'required|numeric|min:0',
            'mkad_price' => 'required|numeric|min:0',
        ]);
        $price->update($data);
        return response()->json(new PriceResource($price));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        $price->delete();
        return response()->noContent();
    }
}
