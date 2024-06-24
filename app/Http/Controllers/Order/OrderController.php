<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\DB;

use App\Enums\LogistOrderStatus;
use App\Enums\ManagerOrderStatus;
use App\Enums\OrderStatusType;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;

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
        $ordersTotal = Order::count();
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $orderExp = '';

        if ($request->has('sorter_key') && in_array($request->get('sorter_key'), ['id', 'created_at', 'started_at'])) {
            $sk = match ($request->get('sorter_key')) {
                'started_at' => "arrive.arrive_time",
                default => "orders." . $request->get('sorter_key'),
            };
            $orderExp = 'order by '. $sk . ($request->has('sorter_order') && $request->get('sorter_order') == 'descend' ? ' desc ' : ' ');
        }

        $idsQuery = <<<SQL
            select orders.id from orders
                left join (SELECT o.id as id, min(obj ->> 'arrive_date') as arrive_time
                    FROM orders o,
                         json_array_elements(o.from_locations::json) obj
                    group by o.id) arrive on orders.id = arrive.id $orderExp limit :per_page offset :offset;
        SQL;

        $query = DB::select($idsQuery, ['per_page' => $perPage, 'offset' => $perPage * ($page - 1)]);

        $ids = collect($query)->pluck('id')->all();
        $orders = Order::findMany($ids);
        $ordersRes = collect([]);

        foreach ($ids as $id) {
            $ordersRes = $ordersRes->push($orders->find($id));
        }

        return [
            'ids' => $ids,
            'data' => OrderResource::collection($ordersRes),
            'meta' => [
                'total' => $ordersTotal,
                'current_page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate(VALIDATE_RULES);
        $data["created_by"] = $request->user()->name;
        $data["updated_by"] = $request->user()->name;
        $order = Order::create($data);
        $this->initOrderStatuses($order->id, $request->user()->name);
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
        $data["updated_by"] = $request->user()->name;
        $order->update($data);
        if (count($order->statuses) == 0) {
            $this->initOrderStatuses($order->id, $request->user()->name);
        }
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

    private function initOrderStatuses($orderId, $userName) {
        $ms = [
            'order_id' => $orderId,
            'type' => OrderStatusType::MANAGER,
            'user' => $userName,
            'status' => ManagerOrderStatus::CREATED,
        ];
        OrderStatus::create($ms);
        $ls = [
            'order_id' => $orderId,
            'type' => OrderStatusType::LOGIST,
            'user' => $userName,
            'status' => LogistOrderStatus::CREATED,
        ];
        OrderStatus::create($ls);

    }
}
