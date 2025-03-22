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
        Schema::create('technical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained();
            $table->date('visit_date'); // Date de la visite
            $table->date('expiration_date'); // Date d’expiration
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_visits');
    }
};
