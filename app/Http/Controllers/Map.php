<?php

namespace App\Http\Controllers;

use App\Model\VehicleRecord;
use Illuminate\Http\Request;

class Map extends Controller
{

    public function show($trip_id, Request $request)
    {

        $vehicle_id = $request->get('vehicle_id', null);

        $javascript = \App::make('JavaScript');

        $recordQuery = VehicleRecord::query()->where('trip_id', '=', $trip_id);
        if (!is_null($vehicle_id)) {
            $recordQuery = $recordQuery->where('vehicle_id', $vehicle_id);
        }

        $records = $recordQuery->orderBy('created_at')->get();
        /*$vehicle_id = $records->first()->vehicle_id;
        $records = $records->filter(function ($record) use ($vehicle_id) {
            return $record->vehicle_id == $vehicle_id;
        });*/
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
