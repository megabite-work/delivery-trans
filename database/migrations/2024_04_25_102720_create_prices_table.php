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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->morphs("owner");
            $table->bigInteger("car_capacity_id");
            $table->string("car_body_type");
            $table->enum("type", ["CLIENT", "CARRIER"]);
            $table->decimal("hourly")->unsigned()->default(0);
            $table->decimal("min_hours")->unsigned()->default(0);
            $table->decimal("hours_for_coming")->unsigned()->default(0);
            $table->decimal("mkad_price")->unsigned()->default(0);
            $table->timestamps();

            $table->unique(["car_capacity_id", "car_body_type", "type", "owner_id", "owner_type"], 'prices_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
