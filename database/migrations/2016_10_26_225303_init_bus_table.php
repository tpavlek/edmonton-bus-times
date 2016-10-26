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
        Schema::create('bus_times', function(Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->double('lat');
            $table->double('lon');
            $table->string('route');
            $table->integer('vehicle_id');
            $table->integer('delay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
