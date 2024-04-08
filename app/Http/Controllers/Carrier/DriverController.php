<?php

namespace App\Http\Controllers\Carrier;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function carrierDriversIndex(Request $request)
    {
        return Driver::where('carrier_id', $request['carrier_id'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "carrier_id" => 'required|exists:App\Models\Carrier,id',
            "surname" => 'required|string',
            "name" => 'required|string',
            "patronymic" => 'nullable|string',
            "birthday" => 'nullable|date',
            "citizenship" => 'nullable|exists:App\Models\Country,code',
            "passport_number" => 'nullable|string',
            "passport_issuer" => 'nullable|string',
            "passport_issuer_code" => 'nullable|string',
            "passport_issue_date" => 'nullable|date',
            "registration_address" => 'nullable|string',
            "phone" => 'nullable|string',
            "email" => 'nullable|string',
            "license_number" => 'nullable|string',
            "license_experition" => 'nullable|date',
            "is_active" => 'nullable|boolean',
        ]);
        $driver = Driver::create($data);
        return response()->json(new DriverResource($driver), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        return new DriverResource($driver);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            "surname" => 'required|string',
            "name" => 'required|string',
            "patronymic" => 'nullable|string',
            "birthday" => 'nullable|date',
            "citizenship" => 'nullable|exists:App\Models\Country,code',
            "passport_number" => 'nullable|string',
            "passport_issuer" => 'nullable|string',
            "passport_issuer_code" => 'nullable|string',
            "passport_issue_date" => 'nullable|date',
            "registration_address" => 'nullable|string',
            "phone" => 'nullable|string',
            "email" => 'nullable|string',
            "license_number" => 'nullable|string',
            "license_experition" => 'nullable|date',
            "is_active" => 'nullable|boolean',
        ]);
        $driver->update($data);
        return response()->json(new DriverResource($driver));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();
        return response()->noContent();
    }
    public function getDriversByCarrierID(Request $request)
    {
        $drivers = DB::table("drivers")
            ->select("id", "surname", "name", "patronymic", "phone", "email", "is_active")
            ->where("carrier_id", $request['carrier_id'])
            ->get();

        return response()->json($drivers);
    }
}
