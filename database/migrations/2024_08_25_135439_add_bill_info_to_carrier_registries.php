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
        Schema::table('carrier_registries', function (Blueprint $table) {
            $table->string('bill_number')->nullable();
            $table->date('bill_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrier_registries', function (Blueprint $table) {
            $table->dropColumn('bill_number');
            $table->dropColumn('bill_date');
        });
    }
};