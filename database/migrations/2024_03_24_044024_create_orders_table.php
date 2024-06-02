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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Cargo
            $table->string('cargo_name')->nullable();
            $table->decimal('cargo_weight')->default(0);
            $table->string('cargo_temp')->nullable();
            $table->boolean('cargo_in_pallets')->default(false);
            $table->integer('cargo_pallets_count')->default(0);
            // Vehicle
            $table->foreignId("car_capacity_id")->nullable()->constrained('car_capacities');
            $table->string('vehicle_body_type')->nullable();
            $table->boolean('vehicle_loading_rear')->default(false);
            $table->boolean('vehicle_loading_lateral')->default(false);
            $table->boolean('vehicle_loading_upper')->default(false);
            // Client
            $table->foreignId("client_id")->nullable()->constrained(table: 'clients');
            $table->smallInteger("client_vat")->default(0);
            // Carrier
            $table->foreignId("carrier_id")->nullable()->constrained(table: 'carriers');
            $table->smallInteger("carrier_vat")->default(0)->unsigned();
            $table->foreignId("carrier_driver_id")->nullable()->constrained(table: 'drivers');
            $table->foreignId("carrier_car_id")->nullable()->constrained(table: 'cars');
            $table->foreignId("carrier_trailer_id")->nullable()->constrained(table: 'cars');
            $table->bigInteger("carrier_odometer_start")->unsigned()->default(0);
            $table->bigInteger("carrier_odometer_end")->unsigned()->default(0);
            // Client Tariff
            $table->decimal("client_tariff_hourly")->nullable();
            $table->decimal("client_tariff_min_hours")->nullable();
            $table->decimal("client_tariff_hours_for_coming")->nullable();
            $table->decimal("client_tariff_mkad_rate")->nullable();
            $table->decimal("client_tariff_mkad_price")->nullable();
            // Carrier Tariff
            $table->decimal("carrier_tariff_hourly")->nullable();
            $table->decimal("carrier_tariff_min_hours")->nullable();
            $table->decimal("carrier_tariff_hours_for_coming")->nullable();
            $table->decimal("carrier_tariff_mkad_rate")->nullable();
            $table->decimal("carrier_tariff_mkad_price")->nullable();
            // JSON Tables
            $table->jsonb("client_expenses")->nullable();
            $table->jsonb("client_discounts")->nullable();

            $table->jsonb("carrier_expenses")->nullable();
            $table->jsonb("carrier_fines")->nullable();

            $table->jsonb("from_locations")->nullable();
            $table->jsonb("to_locations")->nullable();

            $table->jsonb("additional_service")->nullable();
            // Cash
            $table->decimal("client_sum")->default(0);
            $table->boolean('client_sum_calculated')->default(true);
            $table->string('client_sum_author')->nullable();

            $table->decimal("carrier_sum")->default(0);
            $table->boolean('carrier_sum_calculated')->default(true);
            $table->string('carrier_sum_author')->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
