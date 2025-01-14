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
        Schema::create('countries', function (Blueprint $table) {
            $table->string("code")->primary();
            $table->string("name");
        });
        DB::table('countries')->insert(array('code' => 'RUS', 'name' => 'Россия'));
        DB::table('countries')->insert(array('code' => 'BEL', 'name' => 'Белорусь'));
        DB::table('countries')->insert(array('code' => 'KZ', 'name' => 'Казахстан'));
        DB::table('countries')->insert(array('code' => 'UA', 'name' => 'Украина'));
        DB::table('countries')->insert(array('code' => 'UZ', 'name' => 'Узбекистан'));
        DB::table('countries')->insert(array('code' => 'OTHER', 'name' => 'Другое'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
