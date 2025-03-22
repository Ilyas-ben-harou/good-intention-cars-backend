<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();            
            $table->string('marque');
            $table->string('modele');
            $table->integer('dors');
            $table->float('engine_capacity');
            $table->enum('fuel_type',['essence','diesel']);
            $table->enum('type',['automatic','manual']);
            $table->integer('passengers');
            $table->json('photos')->nullable();
            $table->decimal('prixByDay', 8, 2);
            $table->boolean('Disponibilite')->default(true);
            $table->text('description');
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
        Schema::dropIfExists('cars');
    }
}
