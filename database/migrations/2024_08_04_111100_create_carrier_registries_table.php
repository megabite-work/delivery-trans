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
        Schema::create('carrier_registries', function (Blueprint $table) {
            $table->id();
            $table->date("date");
            $table->foreignId("carrier_id")->nullable()->constrained(table: 'carriers');
            $table->decimal("carrier_sum")->default(0);
            $table->decimal("carrier_paid")->default(0);
            $table->smallInteger("vat")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_registries');
    }
};
