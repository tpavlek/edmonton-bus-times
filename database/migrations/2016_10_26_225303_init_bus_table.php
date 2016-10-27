<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitBusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \Schema::create('batches', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->timestamps();
        });

        Schema::create('bus_times', function(Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->uuid('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches');

            $table->double('lat');
            $table->double('lon');
            $table->string('route');
            $table->integer('vehicle_id');
            $table->integer('delay');

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
        \Schema::drop('bus_times');
        \Schema::drop('batches');

    }
}
