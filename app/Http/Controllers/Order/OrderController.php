<?php

namespace App\Http\Controllers\Order;

use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

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
        $joinExp = '';
        $whereExp = 'where 1 = 1';

        $params = ['per_page' => $perPage, 'offset' => $perPage * ($page - 1)];

        if ($request->has('filter')) {
            $f = $request->get('filter');
            if (array_key_exists("id", $f)) {
                $whereExp .= " and orders.id = :id";
                $params['id'] = $f['id'];
            }
            if (array_key_exists("date", $f)) {
                $d1 = Date::parse($f["date"][0])->timezone("Europe/Moscow")->format('Y-m-d');
                $d2 = Date::parse($f["date"][1])->timezone("Europe/Moscow")->format('Y-m-d');
                $whereExp .= " and orders.created_at >= :date1 and orders.created_at <= :date2";
                $params['date1'] = $d1;
                $params['date2'] = $d2;
            }
            if (array_key_exists("arrive_date", $f)) {
                $d1 = Date::parse($f["arrive_date"][0])->timezone("Europe/Moscow")->format('Y-m-d');
                $d2 = Date::parse($f["arrive_date"][1])->timezone("Europe/Moscow")->format('Y-m-d');
                $whereExp .= " and arrive.arrive_time >= :arrive_date1 and arrive.arrive_time <= :arrive_date2";
                $params['arrive_date1'] = $d1;
                $params['arrive_date2'] = $d2;
            }
            if (array_key_exists("status_manager", $f)) {
                $joinExp .= <<<EOD
                    left join (select distinct on (os.order_id) os.order_id, os.status, os.created_at
                    from order_statuses os
                    where type = 'MANAGER'
                    order by os.order_id, os.created_at desc) ms on orders.id = ms.order_id
                EOD;
                $whereExp .= " and ms.status = :status_manager";
                $params['status_manager'] = $f["status_manager"];
            }
            if (array_key_exists("status_logist", $f)) {
                $joinExp .= <<<EOD
                    left join (select distinct on (os.order_id) os.order_id, os.status, os.created_at
                    from order_statuses os
                    where type = 'LOGIST'
                    order by os.order_id, os.created_at desc) ls on orders.id = ls.order_id
                EOD;
                $whereExp .= " and ls.status = :status_logist";
                $params['status_logist'] = $f["status_logist"];
            }
            if (array_key_exists("text", $f)) {
                $joinExp .= <<<EOD
                    left join clients client on orders.client_id = client.id
                    left join carriers carrier on orders.carrier_id = carrier.id
                    left join cars car on orders.carrier_car_id = car.id
                    left join cars trailer on orders.carrier_trailer_id = trailer.id
                EOD;
                $whereExp .= <<<EOD
                    and (client.inn ilike :text or client.name_full ilike :text or client.name_short ilike :text or
                    carrier.inn ilike :text or carrier.name_full ilike :text or carrier.name_short ilike :text or
                    car.name ilike :text or car.plate_number ilike :text or
                    trailer.name ilike :text or trailer.plate_number ilike :text)
                EOD;
                $params["text"] = '%'.$f["text"].'%';
            }
        }

        if ($request->has('sorter_key') && in_array($request->get('sorter_key'), ['id', 'created_at', 'started_at'])) {
            $sk = match ($request->get('sorter_key')) {
                'started_at' => "arrive.arrive_time",
                default => "orders." . $request->get('sorter_key'),
            };
            $orderExp = 'order by '. $sk . ($request->has('sorter_order') && $request->get('sorter_order') == 'descend' ? ' desc ' : ' ');
        }

        $idsQuery = <<<SQL
            select orders.id from orders
                left join (SELECT o.id as id, min(obj ->> 'arrive_date')::date as arrive_time
                    FROM orders o,
                         json_array_elements(o.from_locations::json) obj
                    group by o.id) arrive on orders.id = arrive.id  $joinExp  $whereExp  $orderExp limit :per_page offset :offset;
        SQL;

        $query = DB::select($idsQuery, $params);

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
