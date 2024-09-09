<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_body_types', function (Blueprint $table) {
            $table->string("type")->primary();
        });
        DB::table('car_body_types')->insert(array('type' => 'Тент'));
        DB::table('car_body_types')->insert(array('type' => 'Изотерм'));
        DB::table('car_body_types')->insert(array('type' => 'Фургон'));
        DB::table('car_body_types')->insert(array('type' => 'Рефрижератор'));
        DB::table('car_body_types')->insert(array('type' => 'Борт'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_body_types');
    }
};
