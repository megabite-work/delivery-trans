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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string("name_short");
            $table->string("name_full");
            $table->string("inn", 10)->nullable();
            $table->string("kpp", 9)->nullable();
            $table->string("ogrn", 13)->nullable();
            $table->smallInteger("vat")->default(0);

            $table->string("bik", 9)->nullable();
            $table->string("bank_name")->nullable();
            $table->string("payment_city")->nullable();
            $table->string("account_correspondent")->nullable();
            $table->string("account_payment")->nullable();
        });
        DB::table('companies')->insert(array(
            'name_short' => 'Компания с НДС',
            'name_full' => 'Компания с НДС',
            'inn' => '0000000000',
            'kpp' => '000000000',
            'ogrn' => '0000000000000',
            'vat' => '1',
            'bik' => '',
            'bank_name' => '',
            'payment_city' => '',
            'account_correspondent' => '',
            'account_payment' => '',
        ));
        DB::table('companies')->insert(array(
            'name_short' => 'Компания без НДС',
            'name_full' => 'Компания без НДС',
            'inn' => '0000000000',
            'kpp' => '000000000',
            'ogrn' => '0000000000000',
            'vat' => '0',
            'bik' => '',
            'bank_name' => '',
            'payment_city' => '',
            'account_correspondent' => '',
            'account_payment' => '',
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
