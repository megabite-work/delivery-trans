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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId("carrier_id")->constrained(table: 'carriers')->cascadeOnDelete();
            $table->enum("type", ["TRUCK", "TRACTOR", "TRAILER"]);
            $table->string("plate_number");
            $table->string("name");
            $table->decimal("tonnage")->nullable();
            $table->decimal("volume")->nullable();
            $table->integer("pallets_count")->nullable();
            $table->string("body_type")->nullable();
            $table->boolean("loading_rear")->nullable();
            $table->boolean("loading_lateral")->nullable();
            $table->boolean("loading_upper")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
