<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

// Private Calculator
class CalculationController extends Controller
{
    public function calculate(Request $request) {
        $res = [
            "client" => [
                "sum" => 0,
                "expenses" => 0,
                "discount" => 0,
                "service" => 0,
                "total" => 0,
                "calculated" => true,
            ],
            "carrier" => [
                "sum" => 0,
                "expenses" => 0,
                "fine" => 0,
                "total" => 0,
                "calculated" => true,
            ],
        ];

        $res["client"]["calculated"] = !$request->has('client_sum_calculated') || $request->boolean('client_sum_calculated');
        $res["carrier"]["calculated"] = !$request->has('carrier_sum_calculated') || $request->boolean('carrier_sum_calculated');

        $hh = 0;
        if ($request->has('from_locations') && $request->get('from_locations') != null && (($request->has('to_locations') && $request->get('to_locations') != null) || $request->get('ended_at'))) {
            $mt = null;
            $gt = null;
            foreach ($request->get('from_locations') as $from) {
                if (array_key_exists('arrive_date', $from) && array_key_exists('arrive_time', $from)) {
                    $d = Date::parse($from['arrive_date'], null);
                    $t = Date::parse($from['arrive_time'][0], null);
                    $d->second = 0;
                    $d->hour = $t->hour;
                    $d->minute = $t->minute;
                    if ($mt === null || $d->lessThan($mt)) {
                        $mt = $d;
                    }
                }
            }
            if($request->has('ended_at') && $request->get('ended_at') != null) {
                $gt = Date::parse($request->get('ended_at'), null);
            } else {
                foreach ($request->get('to_locations') as $to) {
                    if (array_key_exists('arrive_date', $to) && array_key_exists('arrive_time', $to)) {
                        $d = Date::parse($to['arrive_date'], null);
                        $t = Date::parse($to['arrive_time'][0], null);
                        $d->second = 0;
                        $d->hour = $t->hour;
                        $d->minute = $t->minute;
                        if ($gt === null || $d->greaterThan($gt)) {
                            $gt = $d;
                        }
                    }
                }
            }
            if($mt !== null && $gt !== null) {
                $hh = $mt->diffInHours($gt);
            }
        }

        // SUM client
        if ($request->has('client_tariff_mkad_price') && $request->has('client_tariff_mkad_rate')) {
            $res["client"]["sum"] += $request->float('client_tariff_mkad_price') * $request->float('client_tariff_mkad_rate');
        }

        if ($request->has('client_tariff_hourly')) {
            $hh_client = $hh;
            if ($request->has('client_tariff_min_hours')) {
                $res["client"]["sum"] += $request->float('client_tariff_hourly') * $request->float('client_tariff_min_hours');
                $hh_client = $hh_client - $request->float('client_tariff_min_hours');
            }
            if ($request->has('client_tariff_hours_for_coming')) {
                $res["client"]["sum"] += $request->float('client_tariff_hourly') * $request->float('client_tariff_hours_for_coming');
            }
            if ($hh_client > 0) {
                $res["client"]["sum"] += $request->float('client_tariff_hourly') * $hh_client;
            }
        }

        // SUM carrier
        if ($request->has('carrier_tariff_mkad_price') && $request->has('carrier_tariff_mkad_rate')) {
            $res["carrier"]["sum"] += $request->float('carrier_tariff_mkad_price') * $request->float('carrier_tariff_mkad_rate');
        }

        if ($request->has('carrier_tariff_hourly')) {
            $hh_carrier = $hh;
            if ($request->has('carrier_tariff_min_hours')) {
                $res["carrier"]["sum"] += $request->float('carrier_tariff_hourly') * $request->float('carrier_tariff_min_hours');
                $hh_carrier = $hh_carrier - $request->float('carrier_tariff_min_hours');
            }
            if ($request->has('carrier_tariff_hours_for_coming')) {
                $res["carrier"]["sum"] += $request->float('carrier_tariff_hourly') * $request->float('carrier_tariff_hours_for_coming');
            }
            if ($hh_carrier > 0) {
                $res["carrier"]["sum"] += $request->float('carrier_tariff_hourly') * $hh_carrier;
            }
        }

        // Expenses
        if($request->has('client_expenses') && $request->get('client_expenses') != null) {
            foreach ($request->get('client_expenses') as $item) {
                if (array_key_exists('v', $item)) {
                    $res["client"]["expenses"] += $item['v'];
                }
            }
        }
        if($request->has('carrier_expenses') && $request->get('carrier_expenses') != null) {
            foreach ($request->get('carrier_expenses') as $item) {
                if (array_key_exists('v', $item)) {
                    $res["carrier"]["expenses"] += $item['v'];
                }
            }
        }

        // Client discounts
        if($request->has('client_discounts') && $request->get('client_discounts') != null ) {
            foreach ($request->get('client_discounts') as $item) {
                if (array_key_exists('v', $item)) {
                    $res["client"]["discount"] += $item['v'];
                }
            }
        }
        // Additional services
        if($request->has('additional_service') && $request->get('additional_service') != null ) {
            foreach ($request->get('additional_service') as $item) {
                if (array_key_exists('v', $item)) {
                    $res["client"]["service"] += $item['v'] * ($item['c'] ?: 1);
                }
            }
        }

        // Carrier fines
        if($request->has('carrier_fines') && $request->get('carrier_fines') != null ) {
            foreach ($request->get('carrier_fines') as $item) {
                if (array_key_exists('v', $item)) {
                    $res["carrier"]["fine"] += $item['v'];
                }
            }
        }

        if ($res["client"]["calculated"]) {
            $res["client"]["total"] =
                $res["client"]["sum"] +
                $res["client"]["expenses"] +
                $res["client"]["service"] -
                $res["client"]["discount"];
        } else {
            if ($request->has("client_sum")) {
                $res["client"]["total"] = $request->float('client_sum');
            }
        }
        if ($res["carrier"]["calculated"]) {
            $res["carrier"]["total"] =
                $res["carrier"]["sum"] +
                $res["carrier"]["expenses"] +
                $res["carrier"]["fine"];
        } else {
            if ($request->has("carrier_sum")) {
                $res["carrier"]["total"] = $request->float('carrier_sum');
            }
        }

        return $res;
    }
}
