<?php

namespace App\Model;

use Illuminate\Support\Collection;

class DailyTrips
{

    private $trips;

    public function __construct()
    {
        $this->trips = new Collection();
    }

    public function init($entity_id)
    {
        if ($this->trips->has($entity_id)) {
            return $this->trips->get($entity_id);
        }

        $sequence = new SequencedStop();
        $this->trips->put($entity_id, $sequence);
        return $sequence;
    }

    public function isEmpty()
    {
        return $this->trips->isEmpty();
    }

    public function saveDay()
    {
        $this->trips->each(function (SequencedStop $sequencedStop) {
            $sequencedStop->save();
        });

        $count = $this->trips->count();

        $this->trips = new Collection();

        return $count;
    }



}
