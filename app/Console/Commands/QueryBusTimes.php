<?php

namespace App\Console\Commands;

use App\VehiclePositions;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class QueryBusTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query-bus-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the current bus times and the delay level';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $positions = new \App\VehiclePositions();

        $data = file_get_contents('https://data.edmonton.ca/download/uzpc-8bnm/application%2Foctet-stream');
        $feed = new \transit_realtime\FeedMessage();
        $feed->parse($data);

        $vehicles = [];

        foreach($feed->getEntityList() as $entity) {

            if ($entity->hasTripUpdate()) {
                $vehicle_id = $entity->getTripUpdate()->getVehicle()->getLabel();
                $route = $entity->getTripUpdate()->getTrip()->route_id;

                if (!$positions->isRealVehicle($vehicle_id, $entity->id)) {
                    // If we're getting a trip update for a route that isn't running on a physical bus, it's not worth the cpu cycles.
                    continue;
                }

                $delay = collect($entity->getTripUpdate()->getStopTimeUpdate())
                    ->filter(function (\transit_realtime\TripUpdate\StopTimeUpdate $stopTime) {

                        // Get rid of the ones from the past.
                        if ($stopTime->hasDeparture()) {
                            if (Carbon::now(new \DateTimeZone('America/Edmonton'))->subMinute()->lt(Carbon::createFromTimestamp($stopTime->getDeparture()->time, new \DateTimeZone('America/Edmonton')))) {
                                return false;
                            }
                        }

                        if ($stopTime->hasArrival()) {
                            if (Carbon::now(new \DateTimeZone('America/Edmonton'))->subMinute()->lt(Carbon::createFromTimestamp($stopTime->getArrival()->time, new \DateTimeZone('America/Edmonton')))) {
                                return false;
                            }
                        }

                        return ($stopTime->hasDeparture() && $stopTime->getDeparture()->hasDelay()) || ($stopTime->hasArrival() && $stopTime->getArrival()->hasDelay());
                    })
                    ->last();

                if ($delay == null) {
                    $delayTime = 0;
                } else {
                    $delayTime = $delay->getArrival()->delay ?? $delay->getDeparture()->delay;
                }

                $vehicles[] = array_merge([
                    'id' => Uuid::uuid4(),
                    'route' => $route,
                    'vehicle_id' => $vehicle_id,
                    'delay' => $delayTime,
                ], $positions->positionFor($vehicle_id));
            }
        }

        \DB::table('bus_times')->insert($vehicles);
    }
}
