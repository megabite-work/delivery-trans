<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrierRegistryResource;
use App\Models\CarrierRegistry;
use App\Models\Order;
use App\Models\Registry;
use Illuminate\Http\Request;

class CarrierRegistryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'carrier_id' => 'required|exists:carrier,id',
            'carrier_sum' => 'numeric',
            'carrier_paid' => 'numeric',
            'vat' => 'required|integer',
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

    public function show(Registry $registry)
    {
        return new CarrierRegistryResource($registry);
    }

    public function update(Request $request, Registry $registry) {
        $data = $request->validate([
            "date" => "required|date",
            "carrier_sum" => "numeric",
            "carrier_paid" => "numeric",
            "vat" => "integer"
        ]);
        $registry->update($data);
        return response()->json(new CarrierRegistryResource($registry),200);
    }

    public function destroy(Registry $registry)
    {
        $registry->orders()->update(['carrier_registry_id' => null]);
        $registry->delete();
        return response()->noContent();
    }

}
