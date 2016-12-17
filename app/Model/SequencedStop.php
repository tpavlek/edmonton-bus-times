<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use transit_realtime\TripUpdate\StopTimeUpdate;

class SequencedStop
{

    private $stops;

    public function __construct()
    {
        $this->stops = new Collection();
    }

    public function record(StopTimeUpdate $stopTimeUpdate, Carbon $timestamp, $meta)
    {
        if ($this->stops->has($stopTimeUpdate->getStopSequence())) {
            return;
        }

        $result = [
            'id' => Uuid::uuid4()->toString(),
            'stop_id' => $stopTimeUpdate->getStopId(),
            'depart_at' => null,
            'depart_delay' => null,
            'arrival_at' => null,
            'arrival_delay' => null,
            'stop_sequence' => $stopTimeUpdate->getStopSequence()
        ];

        if ($stopTimeUpdate->hasDeparture()) {
            $departTime = Carbon::createFromTimestamp($stopTimeUpdate->getDeparture()->time);
            if ($departTime->gt($timestamp)) {
                return;
            }
            $result['depart_at'] = $departTime->toDateTimeString();
            $result['depart_delay'] = $stopTimeUpdate->getDeparture()->delay ?? null;
        }

        if ($stopTimeUpdate->hasArrival()) {
            $arrivalTime = Carbon::createFromTimestamp($stopTimeUpdate->getArrival()->time);

            if ($arrivalTime->gt($timestamp)) {
                return;
            }

            $result['arrival_at'] = $arrivalTime->toDateTimeString();
            $result['arrival_delay'] = $stopTimeUpdate->getArrival()->delay ?? null;
        }

        $this->stops->put($stopTimeUpdate->getStopSequence(), array_merge($meta, $result));
    }

    public function save()
    {
        \DB::table('bus_times')->insert($this->stops->toArray());
    }

}
