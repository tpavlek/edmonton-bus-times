<?php

namespace App\Http\Controllers;

use App\Model\VehicleRecord;

class Map extends Controller
{

    public function show($trip_id)
    {
        $javascript = \App::make('JavaScript');

        $records = VehicleRecord::query()->where('trip_id', '=', $trip_id)->get();
        $last = [];
        $times = $records->reduce(function ($carry, VehicleRecord $record) use (&$last) {
            if (!empty($last) && $last['key'] != $record->status()) {
                $carry[$record->status()][] = $last['value'];
            }

            $carry[$record->status()][] = [ 'lat' => $record->lat, 'lng' => $record->lon ];
            $last = [ 'key' => $record->status(), 'value' =>  [ 'lat' => $record->lat, 'lng' => $record->lon ]];

            return $carry;
        }, ['ontime' => [], 'late' => [], 'early' => []]);

        $javascript->put([
            'times' => $times,
            'center' => [ 'lat' => $records->first()->lat, 'lng' => $records->first()->lon ]
        ]);

        return view('map')->with('times', $times);
    }

}
