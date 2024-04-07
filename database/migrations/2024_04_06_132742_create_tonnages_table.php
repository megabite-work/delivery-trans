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
        Schema::create('tonnages', function (Blueprint $table) {
            $table->id();
            $table->decimal('tonnage')->unsigned()->unique();
        });

        DB::table('tonnage')->insert(array('tonnage' => 1));
        DB::table('tonnage')->insert(array('tonnage' => 1.5));
        DB::table('tonnage')->insert(array('tonnage' => 3));
        DB::table('tonnage')->insert(array('tonnage' => 5));
        DB::table('tonnage')->insert(array('tonnage' => 10));
        DB::table('tonnage')->insert(array('tonnage' => 20));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tonnages');
    }
};
