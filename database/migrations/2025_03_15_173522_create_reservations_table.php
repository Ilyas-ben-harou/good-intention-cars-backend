<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('client_nom_complete');
            $table->string('client_phone');
            $table->foreignId('car_id')->constrained();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->timestamp('date_debut');
            $table->timestamp('date_fin');
            $table->decimal('montantTotal', 10, 2);
            $table->boolean('gps')->default(false);
            $table->boolean('baby_seat')->default(false);
            $table->enum('status_client', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status', ['in progress', 'coming', 'completed'])->default('coming');
            $table->enum('payment_status', ['made', 'not made'])->default('not made');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}
