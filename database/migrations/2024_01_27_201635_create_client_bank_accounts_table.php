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
        Schema::create('client_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId("client_id")->constrained(table: 'clients')->cascadeOnDelete();
            $table->string("bik", 9);
            $table->string("bank_name");
            $table->string("payment_city");
            $table->string("account_correspondent");
            $table->string("account_payment");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_bank_accounts');
    }
};
