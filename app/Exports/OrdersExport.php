<?php

namespace App\Exports;

use App\Enums\LogistOrderStatus;
use App\Enums\ManagerOrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Order::all();
    }

    public function headings(): array
    {
        return [
            '#',
            'Дата',
            'Старт поездки',
            'Статус менеджера',
            'Статус логиста',
            'Заказчик',
            'Перевозчик',
            'Водитель',
            'Авто',
            'Вес груза',
            'Сумма',
            'Себестоимость',
            'Маржа ₽',
            'Маржа %',
            'Откуда',
            'Куда',
        ];
    }

    public function map($order): array
    {
        $statusManager = ManagerOrderStatus::from($order->status_manager->status)->label() . PHP_EOL . $order->status_manager->created_at;
        $statusLogist = LogistOrderStatus::from($order->status_logist->status)->label() . PHP_EOL . $order->status_logist->created_at;
        $driver = $order->driver->sur_name . ' ' . $order->driver->name . PHP_EOL . $order->driver->phone;
        $marginSum = "{$order->margin_sum} ₽";
        $marginPercent = round($order->margin_percent) . ' %';
        // $from = $this->locationToString($order->from_locations);
        // $to = $this->locationToString($order->to_locations);
        
        return [
            $order->id,
            $order->created_at,
            $order->started_at,
            $statusManager,
            $statusLogist,
            $order->client->name_short,
            $order->carrier->name_short,
            $driver,
            $order->car->plate_number,
            $order->cargo_weight,
            $order->client_sum,
            $order->carrier_sum,
            $marginSum,
            $marginPercent,
            // $from,
            // $to,
        ];
    }

    private function locationToString($location = null): string
    {
        $locations = $location ?: [];

        return implode(' \n', array_map(function ($location): string {
            $arriveDate = !empty($location['arrive_time']) ? $location['arrive_time'] : $location['arrive_date'];
            
            return "{$arriveDate} {$location['address']}";
        }, $locations));
    }
}
