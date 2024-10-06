<?php

namespace App\Http\Controllers\Order;

use App\Models\Carrier;
use App\Models\Registry;
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
    'client_tariff_additional_hour_price' => 'nullable|numeric',
    'client_tariff_additional_point_price' => 'nullable|numeric',
    'client_tariff_loading_points' => 'nullable|numeric',
    'client_tariff_unloading_points' => 'nullable|numeric',

    'carrier_tariff_hourly' => 'nullable|numeric',
    'carrier_tariff_min_hours' => 'nullable|numeric',
    'carrier_tariff_hours_for_coming' => 'nullable|numeric',
    'carrier_tariff_mkad_rate' => 'nullable|numeric',
    'carrier_tariff_mkad_price' => 'nullable|numeric',
    'carrier_tariff_additional_hour_price' => 'nullable|numeric',
    'carrier_tariff_additional_point_price' => 'nullable|numeric',
    'carrier_tariff_loading_points' => 'nullable|numeric',
    'carrier_tariff_unloading_points' => 'nullable|numeric',

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

    'ended_at' => 'nullable|date',
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
                $d1 = Date::parse($f["date"][0])->startOfDay()->shiftTimezone("Europe/Moscow");
                $d2 = Date::parse($f["date"][1])->endOfDay()->shiftTimezone("Europe/Moscow");
                $whereExp .= " and orders.created_at >= :date1 and orders.created_at <= :date2";
                $params['date1'] = $d1;
                $params['date2'] = $d2;

            }
            if (array_key_exists("arrive_date", $f)) {
                $d1 = Date::parse($f["arrive_date"][0])->startOfDay();
                $d2 = Date::parse($f["arrive_date"][1])->endOfDay();
                $whereExp .= " and arrive.arrive_time >= :arrive_date1 and arrive.arrive_time <= :arrive_date2";
                $params['arrive_date1'] = $d1;
                $params['arrive_date2'] = $d2;
            }
            if (array_key_exists("status_manager", $f) || array_key_exists("status_manager_date", $f)) {
                $joinExp .= <<<EOD
                    left join (select distinct on (os.order_id) os.order_id, os.status, os.created_at
                    from order_statuses os
                    where type = 'MANAGER'
                    order by os.order_id, os.created_at desc) ms on orders.id = ms.order_id
                EOD;
                if(array_key_exists("status_manager", $f)) {
                    $whereExp .= " and ms.status = :status_manager";
                    $params['status_manager'] = $f["status_manager"];
                }
                if (array_key_exists("status_manager_date", $f)) {
                    $d1 = Date::parse($f["status_manager_date"][0])->startOfDay()->shiftTimezone("Europe/Moscow");
                    $d2 = Date::parse($f["status_manager_date"][1])->endOfDay()->shiftTimezone("Europe/Moscow");
                    $whereExp .=" and ms.created_at >= :status_manager_date1 and ms.created_at <= :status_manager_date2";
                    $params['status_manager_date1'] = $d1;
                    $params['status_manager_date2'] = $d2;
                }
            }
            if (array_key_exists("status_logist", $f) || array_key_exists("status_logist_date", $f)) {
                $joinExp .= <<<EOD
                    left join (select distinct on (os.order_id) os.order_id, os.status, os.created_at
                    from order_statuses os
                    where type = 'LOGIST'
                    order by os.order_id, os.created_at desc) ls on orders.id = ls.order_id
                EOD;
                if (array_key_exists("status_logist", $f)) {
                    $whereExp .= " and ls.status = :status_logist";
                    $params['status_logist'] = $f["status_logist"];
                }
                if (array_key_exists("status_logist_date", $f)) {
                    $d1 = Date::parse($f["status_logist_date"][0])->startOfDay()->shiftTimezone("Europe/Moscow");
                    $d2 = Date::parse($f["status_logist_date"][1])->endOfDay()->shiftTimezone("Europe/Moscow");
                    $whereExp .=" and ls.created_at >= :status_logist_date1 and ls.created_at <= :status_logist_date2";
                    $params['status_logist_date1'] = $d1;
                    $params['status_logist_date2'] = $d2;
                }
            }
            if (array_key_exists("text", $f)) {
                $joinExp .= <<<EOD
                    left join clients client on orders.client_id = client.id
                    left join carriers carrier on orders.carrier_id = carrier.id
                    left join cars car on orders.carrier_car_id = car.id
                    left join cars trailer on orders.carrier_trailer_id = trailer.id
                    left join drivers driver on orders.carrier_driver_id = driver.id
                EOD;
                $whereExp .= <<<EOD
                    and (client.inn ilike :text or client.name_full ilike :text or client.name_short ilike :text or
                    carrier.inn ilike :text or carrier.name_full ilike :text or carrier.name_short ilike :text or
                    car.name ilike :text or car.plate_number ilike :text or
                    trailer.name ilike :text or trailer.plate_number ilike :text or
                    driver.name ilike :text or driver.surname ilike :text or
                    driver.patronymic ilike :text or driver.phone ilike :text or
                    driver.email ilike :text or driver.inn ilike :text or
                    orders.from_locations::text ilike :text or orders.to_locations::text ilike :text)
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

        $listColumns = [
            [
                "title" => "#",
                "key" => "id",
                "width" => 50,
                "sorter" => true,
                "defaultSortOrder" => "descend"
            ],
        ];
        if($request->user()->canDo("ORDERS_LST_COLUMN_CREATED_AT")) {
            $listColumns[] = [
                "title" => "Дата",
                "key" => "created_at",
                "sorter" => true
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_STARTED_AT")) {
            $listColumns[] = [
                "title" => "Старт поездки",
                "key" => "started_at",
                "sorter" => true
            ];
        }

        if($request->user()->canDo("ORDERS_LST_COLUMN_STATUS_MANAGER")) {
            $listColumns[] = [
                "title" => "Статус менеджера",
                "key" => "status_manager"
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_STATUS_LOGIST")) {
            $listColumns[] = [
                "title" => "Статус логиста",
                "key" => "status_logist"
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_CLIENT")) {
            $listColumns[] = [
                "title" => "Заказчик",
                "key" => "client",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_CARRIER")) {
            $listColumns[] = [
                "title" => "Перевозчик",
                "key" => "carrier",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_DRIVER")) {
            $listColumns[] = [
                "title" => "Водитель",
                "key" => "driver",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_VEHICLE")) {
            $listColumns[] = [
                "title" => "Авто",
                "key" => "vehicle",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_WEIGHT")) {
            $listColumns[] = [
                "title" => "Вес груза",
                "key" => "weight",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_CLIENT_SUM")) {
            $listColumns[] = [
                "title" => "Сумма",
                "key" => "client_sum",
                "fixed" => "right"
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_CARRIER_SUM")) {
            $listColumns[] = [
                "title" => "Себестоимость",
                "key" => "carrier_sum",
                "fixed" => "right",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_MARGIN_SUM")) {
            $listColumns[] = [
                "title" => "Маржа ₽",
                "key" => "margin_sum",
                "fixed" => "right",
            ];
        }
        if($request->user()->canDo("ORDERS_LST_COLUMN_MARGIN_PERCENT")) {
            $listColumns[] = [
                "title" => "Маржа %",
                "key" => "margin_percent",
                "fixed" => "right",
            ];
        }
        $res = [];
        foreach($ordersRes as $orderRes) {
            $order = ["id" => $orderRes->id];
            if($request->user()->canDo("ORDERS_LST_COLUMN_CREATED_AT")) {
                $order["created_at"] = $orderRes->created_at;
                $order["updated_at"] = $orderRes->updated_at;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_STARTED_AT")) {
                $order["started_at"] = $orderRes->started_at;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_STATUS_MANAGER")) {
                $order["status_manager"] = $orderRes->status_manager;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_STATUS_LOGIST")) {
                $order["status_logist"] = $orderRes->status_logist;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_CLIENT")) {
                $order["client"] = $orderRes->client;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_CARRIER")) {
                $order["carrier"] = $orderRes->carrier;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_DRIVER")) {
                $order["carrier_driver_id"] = $orderRes->carrier_driver_id;
                $order["carrier_driver"] = $orderRes->driver;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_VEHICLE")) {
                $order["carrier_car_id"] = $orderRes->carrier_car_id;
                $order["carrier_car"] = $orderRes->car;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_WEIGHT")) {
                $order["cargo_weight"] = $orderRes->cargo_weight;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_CLIENT_SUM")) {
                $order["client_sum"] = $orderRes->client_sum;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_CARRIER_SUM")) {
                $order["carrier_sum"] = $orderRes->carrier_sum;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_MARGIN_SUM")) {
                $order["margin_sum"] = $orderRes->margin_sum;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_MARGIN_PERCENT")) {
                $order["margin_percent"] = $orderRes->margin_percent;
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_FROM")) {
                $order["from_locations"] = json_decode($orderRes->from_locations);
            }
            if($request->user()->canDo("ORDERS_LST_COLUMN_TO")) {
                $order["to_locations"] = json_decode($orderRes->to_locations);
            }

            $res[] = $order;
        }

        return [
            'data' => $res,
            'meta' => [
                'listColumns' => $listColumns,
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
        $carrier_id = $request->get('carrier_id', null);
        if ($carrier_id) {
            $carrier = Carrier::find($carrier_id);
            if ($carrier && $carrier->is_resident) {
                $data['carrier_tariff_hourly'] = 0;
                $data['carrier_tariff_min_hours'] = 0;
                $data['carrier_tariff_hours_for_coming'] = 0;
                $data['carrier_tariff_mkad_rate'] = 0;
                $data['carrier_tariff_mkad_price'] = 0;
                $data['carrier_tariff_additional_hour_price'] = 0;
                $data['carrier_tariff_additional_point_price'] = 0;
                $data['carrier_tariff_loading_points'] = 0;
                $data['carrier_tariff_unloading_points'] = 0;
            }
        }
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
        $carrier_id = $request->get('carrier_id', null);
        if ($carrier_id) {
            $carrier = Carrier::find($carrier_id);
            if ($carrier && $carrier->is_resident) {
                $data['carrier_tariff_hourly'] = 0;
                $data['carrier_tariff_min_hours'] = 0;
                $data['carrier_tariff_hours_for_coming'] = 0;
                $data['carrier_tariff_mkad_rate'] = 0;
                $data['carrier_tariff_mkad_price'] = 0;
                $data['carrier_tariff_additional_hour_price'] = 0;
                $data['carrier_tariff_additional_point_price'] = 0;
                $data['carrier_tariff_loading_points'] = 0;
                $data['carrier_tariff_unloading_points'] = 0;
            }
        }
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

    public function setStatusDate(Request $request, OrderStatus $order_status)
    {
        $request->validate(['date' => 'required|date']);
        $order_status->update(['updated_at' => $request->get('date')]);
        return $order_status;
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

    public function getLastOrderDriverCars(Request $request)
    {
        $data = $request->validate([
            "driver_id" => "required|numeric|exists:drivers,id",
            "capacity_id" => "nullable|numeric|exists:car_capacities,id",
        ]);

        if (!key_exists('capacity_id', $data)) {
            $data["capacity_id"] = 0;
        }
        $query = <<<SQL
            select o.id,
                   o.car_capacity_id,
                   o.carrier_car_id,
                   o.carrier_trailer_id,
                   o.carrier_driver_id
            from orders o
                     left join car_capacities cc on cc.id = o.car_capacity_id
                     left join (select * from car_capacities where id = :capacity_id) cc2 on true
            where o.carrier_driver_id = :driver_id
              and o.carrier_car_id is not null
              and coalesce(cc.tonnage >= cc2.tonnage and cc.volume >= cc2.volume and cc.pallets_count >= cc2.pallets_count, false)
            order by o.id desc, o.car_capacity_id nulls last
            limit 1
        SQL;

        $query = DB::select($query, $data);
        if (count($query)>0) {
            return response()->json($query[0]);
        }
        return response()->noContent();
    }

    public function getLastOrderLocation(Request $request)
    {
        $data = $request->validate([
            "client_id" => "required|numeric|exists:clients,id",
        ]);
        $query = <<<SQL
            select o.id,
            o.from_locations
            from orders o
            where o.client_id = :client_id and from_locations NOTNULL
            order by o.id desc
            limit 1
        SQL;

        $query = DB::select($query, $data);
        if (count($query)>0) {
            $fl = json_decode($query[0]->from_locations, true);
            if (count($fl)>0) {
                return [
                    "order_id" => $query[0]->id,
                    "address" => $fl[0]["address"]
                ];
            }
        }
        return response()->noContent();
    }

    public function getAdditionalExpensesSuggestions(Request $request)
    {
        $q = $request->get("q", "");
        $query = <<<SQL
            select distinct s.k
            from ((select ja ->> 'k' as k
                   from orders o,
                        json_array_elements(o.client_expenses::json) ja
                   where ja ->> 'k' ilike :q
                   limit 10)
                  union
                  (select ja ->> 'k' as k
                   from orders o,
                        json_array_elements(o.carrier_expenses::json) ja
                   where ja ->> 'k' ilike :q
                   limit 10)) s
        SQL;
        $query = DB::select($query, ['q' => '%'.$q.'%']);
        return response()->json(collect($query)->pluck('k')->all());
    }
}

