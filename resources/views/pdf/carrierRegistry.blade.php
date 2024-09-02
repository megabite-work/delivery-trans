@php
use Illuminate\Support\Facades\Date;

    function getAddressString($from, $to) {
        $from = json_decode($from);
        $to = json_decode($to);
        $res = [];
        $arr = [];
        if (is_array($from)) {
            $arr = $from;
        }
        if (is_array($to)) {
            $arr = [...$arr, ...$to];
        }
        foreach ($arr as $a) {
            if(property_exists($a, 'address') && trim($a->address) !== '') {
                $res[] = trim($a->address);
            }
        }
        return implode(' – ', $res);
    }

    function gatAdditionalExpenses($exp) {
        $a = json_decode($exp);
        $res = [];
        foreach ($a == null ? [] : $a as $expense) {
            $res[] = $expense->k.' - '.$expense->v.'₽';
        }
        return implode(', ', $res);
    }

    function ordersByDrivers($orders) {
        $res = [];
        foreach ($orders as $order) {
            $res[$order->carrier_driver_id ?? 0]["driver"] = $order->driver ? $order->driver->surname." ".$order->driver->name." ".$order->driver->patronymic : "Без водителя";
            $res[$order->carrier_driver_id ?? 0]["orders"][] = $order;
            $res[$order->carrier_driver_id ?? 0]["sum"] = ($res[$order->carrier_driver_id ?? 0]["sum"] ?? 0) + $order->carrier_sum;
        }
        return $res;
    }
    $vat = ["Без НДС", "НДС", "Нал"];
    $obd = ordersByDrivers($orders);
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th {
            padding: 5px;
            font-size: 14px;
        }
        td {
            padding: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<h2 style="text-align: center; font-size: 20px; line-height: 28px">
    Сведения о работе перевозчика<br/>
    реестр № {{ $registry->id }} от {{ $registry->created_at->format("d.m.Y") }} к счету № {{ $registry->bill_number }} от {{ $registry->bill_date ? Date::parse($registry->bill_date)->format("d.m.Y") : 'без даты' }}
</h2>
<div style="font-size: 18px; line-height: 32px; padding-top: 20px">
    <div style="display: flex; flex-direction: row">
        <div style="width: 130px">Перевозчик:</div>
        <div style="font-weight: bold">{{ $registry->carrier->name_short }} (ИНН: {{ $registry->carrier->inn }})</div>
    </div>
</div>
<div style="padding-top: 20px">
    @foreach($obd as $orderGroup)
        <div style="display: flex; flex-direction: row; padding-top: 50px; padding-bottom: 8px; font-size: 16px;">
            <div style="width: 130px">Водитель:</div>
            <div style="font-weight: bold">{{ $orderGroup["driver"] }}</div>
        </div>
        <table style="width: 100%">
            <thead>
            <tr>
                <th style="text-wrap: nowrap" rowspan="2">№ п/п</th>
                <th rowspan="2">Дата</th>
                <th rowspan="2">Заказчик</th>
                <th style="text-wrap: nowrap" rowspan="2">Номер а/тр</th>
                <th rowspan="2">Тип</th>
                <th colspan="2">Работа</th>
                <th rowspan="2">Часов подачи</th>
                <th rowspan="2">Тип оплаты</th>
                <th rowspan="2">Маршрут</th>
                <th rowspan="2">Тариф за час</th>
                <th rowspan="2">Часы к оплате</th>
                <th rowspan="2">Тариф км МКАД</th>
                <th rowspan="2">Км за МКАД</th>
                <th rowspan="2">Доп. расходы</th>
                <th rowspan="2">Сумма услуги</th>
            </tr>
            <tr>
                <th>с</th>
                <th>по</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orderGroup["orders"] as $order)
                <tr>
                    <td style="text-align: right">{{ $loop->index + 1 }}.</td>
                    <td>{{ $order->created_at->format("d.m.Y") }}<br/># {{ $order->id }}</td>
                    <td>{{ $order->client->name_short ?? '' }}</td>
                    <td>{{$order->car ? $order->car->plate_number : "–"}}</td>
                    <td style="text-wrap: nowrap">{{$order->carCapacity ? $order->carCapacity->tonnage."т. – ".$order->carCapacity->volume."м³. – ".$order->carCapacity->pallets_count."п." : "-"}}</td>
                    <td>{{ $order->time_start ? $order->time_start->format("H:i") : "–" }}</td>
                    <td>{{ $order->time_end ? $order->time_end->format("H:i") : "-" }}</td>
                    <td style="text-align: right">{{ $order->carrier_tariff_hours_for_coming }}</td>
                    <td>{{ $vat[$order->carrier_vat ?? 0] }}</td>
                    <td>{{ getAddressString($order['from_locations'], $order['to_locations']) }}</td>
                    <td style="text-align: right">{{ $order->carrier_tariff_hourly ?? 0 }} ₽</td>
                    <td style="text-align: right">{{ $order->hours['client'] }}</td>
                    <td style="text-align: right">{{ $order->carrier_tariff_mkad_price ?? 0 }} ₽</td>
                    <td style="text-align: right">{{ $order->carrier_tariff_mkad_rate > 0 ? $order->carrier_tariff_mkad_rate : 0 }}</td>
                    <td>{{ gatAdditionalExpenses($order->carrier_expenses) }}</td>
                    <td style="text-align: right">{{ $order->carrier_sum }} ₽</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="15"></td>
                <td style="text-align: right; font-size: 18px;">{{ $orderGroup["sum"] }} ₽</td>
            </tr>
            </tbody>

        </table>
    @endforeach
    <div style="padding: 20px 0; font-size: 18px; font-weight: bold; text-align: right">
        ИТОГО: {{ $registry->carrier_sum }} ₽
    </div>
</div>
<div style="padding-top: 48px; font-size: 18px; display: flex; flex-direction: row">
    <div>От исполнителя: {{ $company->sign_position }}</div>
    <div style="width: 150px; border-bottom: 1px solid; margin-left: 8px"></div>
    <div>{{ $company->sign_name }}</div>
</div>
</body>
</html>