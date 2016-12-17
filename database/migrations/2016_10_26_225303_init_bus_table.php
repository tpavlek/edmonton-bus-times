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

            $table->string('route');
            $table->integer('vehicle_id');

            $table->integer('arrival_delay')->nullable()->default(null);
            $table->timestamp('arrival_at')->nullable()->default(null);
            $table->integer('depart_delay')->nullable()->default(null);
            $table->timestamp('depart_at')->nullable()->default(null);

            $table->integer('stop_id');
            $table->integer('trip_id');

            $table->integer('stop_sequence');

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
