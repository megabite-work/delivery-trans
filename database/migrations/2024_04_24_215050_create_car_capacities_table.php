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
        Schema::create('car_capacities', function (Blueprint $table) {
            $table->id();
            $table->decimal("tonnage")->nullable();
            $table->decimal("volume")->nullable();
            $table->integer("pallets_count")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_capacities');
    }
};
