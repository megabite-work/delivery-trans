<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

use App\Enums\LogistOrderStatus;
use App\Enums\ManagerOrderStatus;
use App\Enums\OrderStatusType;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;

const VALIDATE_RULES = [
    'cargo_name' => 'nullable|string',
    'cargo_weight' => 'nullable|numeric',
    'cargo_temp' => 'nullable|string',
    'cargo_in_pallets' => 'nullable|boolean',
    'cargo_pallets_count' => 'nullable|numeric',

    'car_capacity_id' => 'nullable|exists:App\Models\CarCapacity,id',
    'vehicle_body_type' => 'nullable|exists:App\Models\CarBodyType,type',
    'vehicle_loading_rear' => 'nullable|boolean',
    'vehicle_loading_lateral' => 'nullable|boolean',
    'vehicle_loading_upper' => 'nullable|boolean',

    'client_id' => 'nullable|exists:App\Models\Client,id',
    'client_vat' => 'nullable|numeric',

    'carrier_id' => 'nullable|exists:App\Models\Carrier,id',
    'carrier_vat' => 'nullable|numeric',
    'carrier_driver_id' => 'nullable|exists:App\Models\Driver,id',
    'carrier_car_id' => 'nullable|exists:App\Models\Car,id',
    'carrier_trailer_id' => 'nullable|exists:App\Models\Car,id',
    'carrier_odometer_start' => 'nullable|numeric',
    'carrier_odometer_end' => 'nullable|numeric',

    'client_tariff_hourly' => 'nullable|numeric',
    'client_tariff_min_hours' => 'nullable|numeric',
    'client_tariff_hours_for_coming' => 'nullable|numeric',
    'client_tariff_mkad_rate' => 'nullable|numeric',
    'client_tariff_mkad_price' => 'nullable|numeric',

    'carrier_tariff_hourly' => 'nullable|numeric',
    'carrier_tariff_min_hours' => 'nullable|numeric',
    'carrier_tariff_hours_for_coming' => 'nullable|numeric',
    'carrier_tariff_mkad_rate' => 'nullable|numeric',
    'carrier_tariff_mkad_price' => 'nullable|numeric',

    'client_expenses' => 'nullable|JSON',
    'client_discounts' => 'nullable|JSON',

    'carrier_expenses' => 'nullable|JSON',
    'carrier_fines' => 'nullable|JSON',

    'from_locations' => 'nullable|JSON',
    'to_locations' => 'nullable|JSON',

    'additional_service' => 'nullable|JSON',

    'client_sum' => 'nullable|numeric',
    'client_sum_calculated' => 'nullable|boolean',
    'carrier_sum' => 'nullable|numeric',
    'carrier_sum_calculated' => 'nullable|boolean',
];

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return OrderResource::collection(Order::orderByDesc('id')->paginate($request['per_page']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate(VALIDATE_RULES);

        $order = Order::create($data);
        return response()->json(new OrderResource($order), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate(VALIDATE_RULES);
        $order->update($data);

        return response()->json(new OrderResource($order));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }

    public function setStatus(Request $request, Order $order) {
        $data = $request->validate([
            'type' => ['required', new Enum(OrderStatusType::class)],
            'status' => [
                'required',
                new Enum(OrderStatusType::tryFrom($request->type) == OrderStatusType::MANAGER
                    ? ManagerOrderStatus::class
                    : LogistOrderStatus::class)
            ],
        ]);
        $user = $request->user();
        $data['user'] = $user->name;
        $data['order_id'] = $order->id;

        $status = OrderStatus::create($data);
        return response()->json($status, 201);
    }
}
