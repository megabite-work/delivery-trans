<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRegistryResource;
use App\Models\Order;
use App\Models\Registry;
use Illuminate\Http\Request;

class RegistryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'client_id' => 'required|exists:clients,id',
            'client_sum' => 'numeric',
            'client_paid' => 'numeric',
            'vat' => 'required|integer',
            'order_ids' => 'required|array',
        ]);
        $orders = Order::findMany($data['order_ids'])->where("registry_id", null);
        if ($orders->count() < 1) {
            return response()->json(null, 400);
        }
        $registry = Registry::create($data);
        foreach ($orders as $order) {
            $order->update(["registry_id" => $registry->id]);
        }
        return response()->json(new ClientRegistryResource($registry),201);
    }

    public function show(Registry $registry)
    {
        return new ClientRegistryResource($registry);
    }

    public function update(Request $request, Registry $registry) {
        $data = $request->validate([
            "date" => "required|date",
            "client_sum" => "numeric",
            "client_paid" => "numeric",
            "vat" => "integer"
        ]);
        $registry->update($data);
        return response()->json(new ClientRegistryResource($registry),200);
    }

    public function destroy(Registry $registry)
    {
        $registry->orders()->update(['registry_id' => null]);
        $registry->delete();
        return response()->noContent();
    }

}
