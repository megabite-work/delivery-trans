<?php

namespace App\Http\Controllers\Dir;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Client;
use App\Models\AdditionalService;
use App\Models\DefaultPrice;
use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;

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

    public function priceSuggest(Request $request)
    {
        $q = <<<SQL
            select distinct on (a.name) a.name, p.price
            from additional_services a
                     left join
                 (select distinct on (asf.name) asf.id,
                                                asf.name                                                           as name,
                                                asf.price                                                          as price,
                                                coalesce(dp.is_default, false)                                     as is_default,
                                                asf.owner_type = 'App\Models\Client' AND asf.owner_id = :client_id as is_client
                  from additional_services asf
                           left join default_prices dp on asf.owner_type = 'App\Models\DefaultPrice' AND
                                                          asf.owner_id = dp.id
                  where dp.is_default
                     or (asf.owner_type = 'App\Models\Client' AND asf.owner_id = :client_id)
                  order by asf.name, is_client desc) p on a.id = p.id
            where a.name ilike :name
            order by a.name, p.price
        SQL;
        $params['name'] = '%'.$request->get('q', '').'%';
        $params['client_id'] = $request->get('client_id', 0);
        $query = DB::select($q, $params);
        return response()->json($query);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeForClient(Request $request)
    {
        $data = $request->validate([
            "name" => "string|required",
            "price" => "numeric|nullable",
        ]);
        $data["owner_type"] = Client::class;
        $data["owner_id"] = $request["client_id"];

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
