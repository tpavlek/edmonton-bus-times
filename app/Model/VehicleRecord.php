<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VehicleRecord extends Model
{

    public $guarded = [];
    public $incrementing = false;
    public $table = "bus_times";

    public function status($early_threshold = 180, $late_threshold = 180)
    {
        if ($this->delay > $late_threshold) {
            return 'late';
        }

        if ($this->delay < ($early_threshold * -1)) {
            return 'early';
        }

        return 'ontime';
    }

    public static function current()
    {

    }

}
