<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VehicleRecord extends Model
{

    public $guarded = [];
    public $incrementing = false;
    public $table = "bus_times";

    public static function current()
    {

    }

}
