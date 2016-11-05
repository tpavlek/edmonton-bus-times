<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property \Illuminate\Database\Eloquent\Collection times
 */
class Batch extends Model
{

    public $guarded = [];
    public $incrementing = false;

    public static function init()
    {
        return static::create([
            'id' => Uuid::uuid4()->toString()
        ]);
    }

    /**
     * @return Batch
     */
    public static function current()
    {
        return static::query()->orderBy('created_at', 'DESC')->first();
    }

    public function rollup($early_threshold = 180, $late_threshold = 180)
    {
        $times = $this->times;
        return [
            'on-time' => $times->filter(function (VehicleRecord $vehicleRecord) use ($early_threshold, $late_threshold) {
                return $vehicleRecord->delay > ($early_threshold * -1) && $vehicleRecord->delay < $late_threshold;
            }),
            'early' => $times->filter(function (VehicleRecord $vehicleRecord) use ($early_threshold) {
                return $vehicleRecord->delay < ($early_threshold * -1);
            }),
            'late' => $times->filter(function (VehicleRecord $vehicleRecord) use ($late_threshold) {
                return $vehicleRecord->delay > $late_threshold;
            })
        ];
    }

    public function times()
    {
        return $this->hasMany(VehicleRecord::class, 'batch_id', 'id');
    }


}
