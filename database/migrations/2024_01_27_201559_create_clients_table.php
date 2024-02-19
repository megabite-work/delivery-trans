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
        Schema::create('clients', function (Blueprint $table) {
            $table->id()->startingValue(1741511354);
            $table->string("name_short");
            $table->string("name_full");
            $table->enum("type", ["LEGAL", "INDIVIDUAL"]);
            $table->string("inn", 12)->unique();
            $table->string("kpp", 9)->nullable();
            $table->string("ogrn", 15)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
