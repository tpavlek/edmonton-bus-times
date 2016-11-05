<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVehiclePositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_positions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->double('lat');
            $table->double('lon');

            $table->integer('vehicle_id');
            $table->integer('trip_id');
            $table->dateTime('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vehicle_positions');
    }
}
