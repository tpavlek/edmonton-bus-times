<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Laracasts\Utilities\JavaScript\PHPToJavaScriptTransformer;

class OnTime extends Controller
{


    public function ninetyninestreet()
    {
        $javascript = \App::make('JavaScript');
        $lat = [ 53.518126, 53.527295 ];
        $lon = [ -113.487380, -113.484540 ];
        /** @var ConnectionInterface $db */
        $db = \DB::connection();

        $result = $db->table('bus_times')
            ->whereBetween('lat', $lat)
            ->whereBetween('lon', $lon)
            ->whereBetween($db->raw('hour(created_at)'), [ 4, 23 ])
            ->whereBetween($db->raw('dayofweek(created_at)'), [ 2, 6 ])
            ->select([
                $db->raw('date(created_at) as day'),
                $db->raw('hour(created_at) as hour'),
                'trip_id',
                'route',
                'vehicle_id',
                $db->raw("case when min(delay) < -180 then 'EARLY' when max(delay) > 180 then 'LATE' else 'ONTIME' END as status")
            ])
            ->groupBy([
                'day',
                'hour',
                'route',
                'vehicle_id',
                'trip_id',
            ])
            ->get()
            ->groupBy('status')
            ->map(function ($group) {
                $hourGroups = $group->groupBy(function ($result) {
                    return Carbon::createFromTime($result->hour)->format('gA');
                })->map(function ($hourGroup) {
                    return $hourGroup->groupBy('route')->map(function ($routeGroup) {
                        return $routeGroup->count();
                    });
                });

                return $hourGroups;
            });


        $series = [];

        $categories = $result->keys()->toArray();

        $result->each(function ($hourGroup, $key) use (&$series, $categories) {

            foreach ($hourGroup as $hour => $routes) {
                if (!isset($series[$hour])) {
                    $series[$hour] = [ 0, 0, 0];
                }

                $series[$hour][array_search($key, $categories)] += $routes->count();
            }
        });

        $series = collect($series)->map(function($series, $key) {
            return [ 'name' => $key, 'data' => $series ];
        })->values()->toArray();

        $javascript->put([
            'categories' => $categories,
            'series' => $series
        ]);

        return view('ontime-chart');
    }

}
