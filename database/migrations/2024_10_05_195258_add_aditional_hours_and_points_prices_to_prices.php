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
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('additional_hour_price')->unsigned()->default(0);
            $table->decimal('additional_point_price')->unsigned()->default(0);
            $table->integer('loading_points')->unsigned()->default(0);
            $table->integer('unloading_points')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('additional_hour_price');
            $table->dropColumn('additional_point_price');
            $table->dropColumn('loading_points');
            $table->dropColumn('unloading_points');
        });
    }
};
