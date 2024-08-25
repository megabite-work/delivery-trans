<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrierRegistryResource;
use App\Models\CarrierRegistry;
use App\Models\Order;
use Illuminate\Http\Request;

class CarrierRegistryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'carrier_id' => 'required|exists:carriers,id',
            'carrier_sum' => 'numeric',
            'carrier_paid' => 'numeric',
            'vat' => 'required|integer',
            'bill_number' => 'nullable|string',
            'bill_date' => 'nullable|date',
            'order_ids' => 'required|array',
        ]);
        $orders = Order::findMany($data['order_ids'])->where("carrier_registry_id", null);
        if ($orders->count() < 1) {
            return response()->json(null, 400);
        }
        $registry = CarrierRegistry::create($data);
        foreach ($orders as $order) {
            $order->update(["carrier_registry_id" => $registry->id]);
        }
        return response()->json(new CarrierRegistryResource($registry),201);
    }

    public function show(CarrierRegistry $carrierRegistry)
    {
        return new CarrierRegistryResource($carrierRegistry);
    }

    public function update(Request $request, CarrierRegistry $carrierRegistry) {
        $data = $request->validate([
            "date" => "required|date",
            "carrier_sum" => "numeric",
            "carrier_paid" => "numeric",
            "vat" => "integer",
            'bill_number' => 'nullable|string',
            'bill_date' => 'nullable|date',
        ]);
        $carrierRegistry->update($data);
        return response()->json(new CarrierRegistryResource($carrierRegistry),200);
    }

    public function destroy(CarrierRegistry $carrierRegistry)
    {
        $carrierRegistry->orders()->update(['carrier_registry_id' => null]);
        $carrierRegistry->delete();
        return response()->noContent();
    }

}
