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

        $results = \DB::table('bus_times')
            ->join('stops', 'stops.id', '=', 'bus_times.stop_id')
            ->where('trip_id', [ $trip_id, $trip_id + 1])
            ->orderBy('created_at')
            ->get();

        $last = [];
        $times = $results->reduce(function ($carry, $record) use (&$last) {
            if ($record->depart_delay < -60) {
                $status = 'early';
            } else if ($record->depart_delay > 60) {
                $status = 'late';
            } else {
                $status = 'ontime';
            }
            if (!empty($last) && $last['key'] != $status) {
                $carry[$status][] = $last['value'];
            }

            $carry[$status][] = [ 'lat' => doubleval($record->lat), 'lng' => doubleval($record->lon) ];
            $last = [ 'key' => $status, 'value' =>  [ 'lat' => doubleval($record->lat), 'lng' => doubleval($record->lon) ]];

            return $carry;
        }, ['ontime' => [], 'late' => [], 'early' => []]);


        $javascript->put([
            'times' => $times,
            'center' => [ 'lat' => doubleval($results->first()->lat), 'lng' => doubleval($results->first()->lon) ]
        ]);

        return view('map')->with('times', $times);
    }

}
