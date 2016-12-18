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
            ->where('trip_id', $trip_id)
            ->orderBy('stop_sequence')
            ->get();

        $last = [];
        $times = $results->reduce(function ($carry, $record) use (&$last) {

            if ($record->depart_delay < -60) {
                $status = 'early';
                $color = '#68c1b7';
            } else if ($record->depart_delay > 60) {
                $status = 'late';
                $color = '#af180e';
            } else {
                $status = 'ontime';
                $color = '#1e7005';
            }

            $currentPosition = [ 'lat' => doubleval($record->lat), 'lng' => doubleval($record->lon) ];

            if (empty($last)) {
                $carry[] = [ 'values' => [ $currentPosition ], 'color' => $color];
            } else if ($last['key'] != $status) {
                $carry[] = [ 'values' => [ $last['value'],  $currentPosition ], 'color' => $color];
            } else {
                $finalItem = array_pop($carry);
                $finalItem['values'][] = $currentPosition;
                $carry[] = $finalItem;
            }


            $last = [ 'key' => $status, 'value' =>  $currentPosition ];

            return $carry;
        }, []);

       // dd($times);


        $javascript->put([
            'times' => $times,
            'center' => [ 'lat' => doubleval($results->first()->lat), 'lng' => doubleval($results->first()->lon) ]
        ]);

        return view('map')->with('times', $times);
    }

}
