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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId("carrier_id")->constrained(table: 'carriers')->cascadeOnDelete();
            $table->string("surname");
            $table->string("name");
            $table->string("patronymic")->nullable();
            $table->date("birthday")->nullable();
            $table->string("citizenship")->default("RUS");
            $table->string("passport_number")->nullable()->unique();
            $table->string("passport_issuer")->nullable();
            $table->string("passport_issuer_code")->nullable();
            $table->date("passport_issue_date")->nullable();
            $table->string("registration_address")->nullable();
            $table->string("phone")->nullable();
            $table->string("email")->nullable();
            $table->string("license_number")->nullable();
            $table->date("license_expiration")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
