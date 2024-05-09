<?php

namespace App\Http\Controllers\Price;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultPriceResource;
use App\Models\DefaultPrice;
use Illuminate\Http\Request;

class DefaultPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DefaultPriceResource::collection(DefaultPrice::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);
        $default_price = DefaultPrice::create($data);
        return response()->json(new DefaultPriceResource($default_price), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DefaultPrice $default_price)
    {
        return new DefaultPriceResource($default_price);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DefaultPrice $default_price)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);
        $default_price->update($data);
        return response()->json(new DefaultPriceResource($default_price));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DefaultPrice $default_price)
    {
        $default_price->delete();
        return response()->noContent();
    }
}
