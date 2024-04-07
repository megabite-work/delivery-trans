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
        Schema::create('tconditions', function (Blueprint $table) {
            $table->id();
            $table->smallInteger("min")->nullable();
            $table->smallInteger("max")->nullable();
        });
        DB::table('tconditions')->insert(array('min' => 0, 'max' => 2));
        DB::table('tconditions')->insert(array('min' => -2, 'max' => 0));
        DB::table('tconditions')->insert(array('min' => -7, 'max' => -2));
        DB::table('tconditions')->insert(array('min' => -15, 'max' => -7));
        DB::table('tconditions')->insert(array('min' => -18, 'max' => -15));

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tconditions');
    }
};
