<?php

namespace App\Http\Controllers\Dir;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;
use App\Models\Client;
use App\Models\DefaultPrice;
use App\Models\AdditionalService;
use Illuminate\Support\Facades\DB;

class AdditionalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(AdditionalService::all());
    }

    public function nameSuggest(Request $request)
    {
        $q = <<<SQL
            select distinct name from additional_services where name ilike :name
        SQL;

        $params['name'] = '%'.$request->get('q', '').'%';
        $query = DB::select($q, $params);
        return collect($query)->pluck('name')->all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeForClient(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:App\Models\Client,id',
            "name" => "string|required",
            "price" => "numeric|nullable",
        ]);
        $data["owner_type"] = Client::class;
        $data["owner_id"] = $data["client_id"];

        $service = AdditionalService::create($data);
        return response()->json(new DTApiResource($service), 201);
    }

    public function storeForDefault(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'price' => 'numeric|nullable',
        ]);
        $data["owner_type"] = DefaultPrice::class;
        $data["owner_id"] = $request["price_id"];
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
    public function update(Request $request, AdditionalService $additional_service)
    {
        $data = $request->validate([
            "name" => "string|required",
            "price" => "numeric:2|nullable",
        ]);
        $additional_service->update($data);
        return response()->json(new DTApiResource($additional_service));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdditionalService $additional_service)
    {
        $additional_service->delete();
        return response()->noContent();
    }
}
