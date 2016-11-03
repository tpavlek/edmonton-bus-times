<?php

namespace App;

use Carbon\Carbon;

class VehiclePositions
{
    /** @var \Illuminate\Support\Collection  */
    public $entites;

    public function __construct($batch_id)
    {
        $data = file_get_contents('https://data.edmonton.ca/download/7qed-k2fc/application%2Foctet-stream');
        file_put_contents(storage_path() . "/update-files/" . Carbon::now()->timestamp . "-" . $batch_id . "-positions.pb", $data);
        $feed = new \transit_realtime\FeedMessage();
        $feed->parse($data);

        $this->entites = collect($feed->getEntityList());
    }

    public function isRealVehicle($vehicle_id, $trip_id)
    {
        return $this->entites->contains(function($entity) use ($vehicle_id, $trip_id) {
            return $entity->id == $vehicle_id && $entity->getVehicle()->getTrip()->trip_id == $trip_id;
        });
    }

    public function positionFor($vehicle_id)
    {
        $entity = $this->entites->first(function ($entity) use ($vehicle_id) {
            return $entity->id == $vehicle_id;
        });

        if ($entity == null) {
            return [ 'lat' => null, 'lon' => null ];
        }

        return [ 'lat' => $entity->getVehicle()->getPosition()->getLatitude(), 'lon' => $entity->getVehicle()->getPosition()->getLongitude() ];
    }

}
