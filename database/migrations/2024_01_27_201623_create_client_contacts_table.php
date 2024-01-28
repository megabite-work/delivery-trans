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
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId("client_id")->constrained(table: 'clients')->cascadeOnDelete();
            $table->enum("type", [
                "PHONE",
                "EMAIL",
                "PERSON",
                "MESSENGER",
                "ADDRESS",
                "WEB",
                "OTHER"
            ]);
            $table->text("value")->nullable();
            $table->text("note")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};
