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
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: "DejaVu Sans";
            font-size: 9pt;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th {
            padding: 5px;
            font-size: 7pt;
        }
        td {
            padding: 5px;
            font-size: 7pt;
        }
    </style>
</head>
<body>
<h2 style="text-align: center; font-size: 9pt">
    Сведения о работе автотранспорта<br/>
    реестр № {{ $registry->id }} от {{ $registry->created_at->format("d.m.Y") }} к счету № {{ $registry->bill_number }} от {{ Date::parse($registry->bill_date)->format("d.m.Y") }}
</h2>
<div style="font-size: 9pt; padding-top: 20px">
    <div>
        <span>Заказчик:</span>
        <span style="font-weight: bold">{{ $registry->client->name_short }} (ИНН: {{ $registry->client->inn }})</span>
    </div>
    <div>
        <span>Исполнитель:</span>
        <span style="font-weight: bold">{{ $company->name_short }} (ИНН: {{ $company->inn }})</span>
    </div>
</div>
<div style="padding-top: 20px">
    <table style="width: 100%">
        <thead>
        <tr>
            <th style="text-wrap: nowrap" rowspan="2"></th>
            <th rowspan="2">Дата</th>
            <th rowspan="2">Водитель</th>
            <th style="text-wrap: nowrap" rowspan="2">Номер а/тр</th>
            <th rowspan="2">Тип</th>
            <th colspan="2">Работа</th>
            <th rowspan="2">Часов подачи</th>
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
            @foreach($orders == null ? [] : $orders as $order)
                <tr>
                    <td style="text-align: right">{{ $loop->index + 1 }}.</td>
                    <td>{{ $order->created_at->format("d.m.Y") }}<br/># {{ $order->id }}</td>
                    <td style="text-align: center">{{ $order->driver ? $order->driver->surname." ".$order->driver->name." ".$order->driver->patronymic : "–" }} </td>
                    <td>{{$order->car ? $order->car->plate_number : "–"}}</td>
                    <td style="text-wrap: nowrap">{{$order->carCapacity ? $order->carCapacity->tonnage."т. – ".$order->carCapacity->volume."м³. – ".$order->carCapacity->pallets_count."п." : "-"}}</td>
                    <td>{{ $order->time_start ? $order->time_start->format("H:i") : "–" }}</td>
                    <td>{{ $order->time_end ? $order->time_end->format("H:i") : "-" }}</td>
                    <td style="text-align: right">{{ $order->client_tariff_hours_for_coming }}</td>
                    <td>{{ getAddressString($order['from_locations'], $order['to_locations']) }}</td>
                    <td style="text-align: right">{{ $order->client_tariff_hourly ?? 0 }}₽</td>
                    <td style="text-align: right">{{ $order->hours['client'] }}</td>
                    <td style="text-align: right">{{ $order->client_tariff_mkad_price ?? 0 }}₽</td>
                    <td style="text-align: right">{{ $order->client_tariff_mkad_rate > 0 ? $order->client_tariff_mkad_rate : 0 }}</td>
                    <td>{{ gatAdditionalExpenses($order->client_expenses) }}</td>
                    <td style="text-align: right">{{ $order->client_sum }}₽</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="14" scope="row" style="text-align: right;">ИТОГО:</th>
            <td style="text-align: right; font-size: 9pt; font-weight: bold"><nobr>{{ $registry->client_sum }} ₽</nobr></td>
        </tr>
        </tfoot>
    </table>
</div>
<div style="padding-top: 48px; font-size: 9pt;">
    <span>От исполнителя: {{ $company->sign_position }}</span>
    <span style="border-bottom: 1px solid; margin-left: 8px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    <span>{{ $company->sign_name }}</span>
</div>
</body>
</html>
