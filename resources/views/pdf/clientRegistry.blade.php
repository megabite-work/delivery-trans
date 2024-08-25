@php

function getAddressString($from, $to) {
    $from = json_decode($from);
    $to = json_decode($to);
    $res = [];
    foreach ([...$from, ...$to] as $a) {
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
    <title>{{ $title }}</title>
    <style>
        html {
            font-family: DejaVu Sans;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th {
            padding: 5px;
        }
        td {
            padding: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<h2 style="text-align: center; font-size: 20px; line-height: 28px">
    Сведения о работе автотранспорта<br/>
    реестр № {{ $registry->id }} от {{ $registry->created_at->format("d.m.Y") }} к счету № {{ $registry->bill_number }} от {{ Illuminate\Support\Facades\Date::parse($registry->bill_date)->format("d.m.Y") }}
</h2>
<div style="font-size: 18px; line-height: 32px; padding-top: 20px">
    <div style="display: flex; flex-direction: row">
        <div style="width: 130px">Заказчик:</div>
        <div style="font-weight: bold">{{ $registry->client->name_short }} (ИНН: {{ $registry->client->inn }})</div>
    </div>
    <div style="display: flex; flex-direction: row">
        <div style="width: 130px">Исполнитель:</div>
        <div style="font-weight: bold">{{ $company->name_short }} (ИНН: {{ $company->inn }})</div>
    </div>
</div>
<div style="padding-top: 20px">
    <table style="width: 100%">
        <thead>
        <tr>
            <th style="text-wrap: nowrap" rowspan="2">№ п/п</th>
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
                    <td>{{ $order->time_start->format("H:i") }}</td>
                    <td>{{ $order->time_end->format("H:i") }}</td>
                    <td style="text-align: right">{{ $order->client_tariff_hours_for_coming }}</td>
                    <td>
                        {{ getAddressString($order['from_locations'], $order['to_locations'])  }}
                    </td>
                    <td style="text-align: right">{{ $order->client_tariff_hourly }} ₽</td>
                    <td style="text-align: right">{{ $order->clientHours }}</td>
                    <td style="text-align: right">{{ $order->client_tariff_mkad_price }} ₽</td>
                    <td style="text-align: right">{{ $order->client_tariff_mkad_rate > 0 ? $order->client_tariff_mkad_rate : 0 }}</td>
                    <td>{{ gatAdditionalExpenses($order->client_expenses) }}</td>
                    <td style="text-align: right">{{ $order->client_sum }} ₽</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="14" scope="row" style="text-align: right;">ИТОГО:</th>
            <td style="text-align: right; font-size: 16px; font-weight: bold">{{ $registry->client_sum }} ₽</td>
        </tr>
        </tfoot>
    </table>
</div>
<div style="padding-top: 48px; font-size: 18px; display: flex; flex-direction: row">
    <div>От исполнителя: {{ $company->sign_position }}</div>
    <div style="width: 150px; border-bottom: 1px solid; margin-left: 8px"></div>
    <div>{{ $company->sign_name }}</div>
</div>
</body>
</html>
