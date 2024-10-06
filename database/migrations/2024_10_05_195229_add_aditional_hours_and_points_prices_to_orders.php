<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('client_tariff_additional_hour_price')->unsigned()->default(0);
            $table->decimal('client_tariff_additional_point_price')->unsigned()->default(0);
            $table->integer('client_tariff_loading_points')->unsigned()->default(0);
            $table->integer('client_tariff_unloading_points')->unsigned()->default(0);

            $table->decimal('carrier_tariff_additional_hour_price')->unsigned()->default(0);
            $table->decimal('carrier_tariff_additional_point_price')->unsigned()->default(0);
            $table->integer('carrier_tariff_loading_points')->unsigned()->default(0);
            $table->integer('carrier_tariff_unloading_points')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('client_tariff_additional_hour_price');
            $table->dropColumn('client_tariff_additional_point_price');
            $table->dropColumn('client_tariff_loading_points');
            $table->dropColumn('client_tariff_unloading_points');

            $table->dropColumn('carrier_tariff_additional_hour_price');
            $table->dropColumn('carrier_tariff_additional_point_price');
            $table->dropColumn('carrier_tariff_loading_points');
            $table->dropColumn('carrier_tariff_unloading_points');
        });
    }
};
