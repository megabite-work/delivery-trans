<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Enums\ClientType;
use App\Http\Resources\CarrierResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return CarrierResource::collection(
            Carrier::where('name_short', 'ilike', '%'.$request->get('filter', '').'%')
                ->orWhere('name_full', 'ilike', '%'.$request->get('filter', '').'%')
                ->orWhere('inn', 'ilike', '%'.$request->get('filter', '').'%')
                ->orWhere('kpp', 'ilike', '%'.$request->get('filter', '').'%')
                ->paginate($request['per_page'])
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_short' => 'required|string',
            'name_full' => 'required|string',
            'type' => ['required', new Enum(ClientType::class)],
            'inn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'required|digits:10|unique:App\Models\Carrier,inn':
                'required|digits:12|unique:App\Models\Carrier,inn',
            'kpp' => 'nullable|digits:9',
            'ogrn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'nullable|digits:13':
                'nullable|digits:15',
            'ogrnip_date' => 'nullable|date',
            'vat' => 'integer|nullable',
            'is_resident' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $carrier = Carrier::create($data);
        return response()->json(new CarrierResource($carrier),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carrier $carrier)
    {
        return new CarrierResource($carrier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Carrier $carrier)
    {
        $data = $request->validate([
            'name_short' => 'required|string',
            'name_full' => 'required|string',
            'type' => ['required', new Enum(ClientType::class)],
            'inn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'required|digits:10':
                'required|digits:12',
            'kpp' => 'nullable|digits:9',
            'ogrn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'nullable|digits:13':
                'nullable|digits:15',
            'ogrnip_date' => 'nullable|date',
            'vat' => 'integer|nullable',
            'is_resident' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $carrier->update($data);
        return response()->json(new CarrierResource($carrier));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();
        return response()->noContent();
    }

    public function searchSuggest(Request $request) {
        $carriers = DB::table("carriers")
            ->select("id", "name_full", "name_short", "inn", "vat", "is_active")
            ->where("name_short", "ilike", "%{$request['q']}%")
            ->orWhere("name_full", "ilike", "%{$request['q']}%")
            ->orWhere("inn", "ilike", "%{$request['q']}%")
            ->limit(25)
            ->get();

        return response()->json($carriers);
    }
}
