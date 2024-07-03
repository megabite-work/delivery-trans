<?php

namespace App\Http\Controllers\Dir;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;
use App\Models\Client;
use App\Models\DefaultPrice;
use App\Models\AdditionalService;

class AdditionalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(AdditionalService::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeForClient(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:App\Models\Client,id',
            "name" => "string|required",
            "price" => "decimal:2|nullable",
        ]);
        $data["owner_type"] = Client::class;
        $data["owner_id"] = $data["client_id"];

        $service = AdditionalService::create($data);
        return response()->json(new DTApiResource($service), 201);
    }

    public function storeForDefault(Request $request)
    {
        $data = $request->validate([
            'price_id' => 'required|exists:App\Models\DefaultPrice,id',
            "name" => "string|required",
            "price" => "decimal:2|nullable",
        ]);
        $data["owner_type"] = DefaultPrice::class;
        $data["owner_id"] = $data["client_id"];

        $service = AdditionalService::create($data);
        return response()->json(new DTApiResource($service), 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(AdditionalService $service)
    {
        return response()->json(new DTApiResource($service));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdditionalService $service)
    {
        $data = $request->validate([
            "name" => "string|required",
            "price" => "decimal:2|nullable",
        ]);
        $service->update($data);
        return response()->json(new DTApiResource($service));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdditionalService $service)
    {
        $service->delete();
        return response()->noContent();
    }
}
