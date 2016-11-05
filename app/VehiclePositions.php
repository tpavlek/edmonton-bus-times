<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use transit_realtime\VehiclePosition\VehicleStopStatus;

class VehiclePositions
{
    /** @var \Illuminate\Support\Collection  */
    public $entites;

    public function __construct(Collection $entityList)
    {
        $this->entites = $entityList;
    }

    public static function fromData($fileData)
    {
        $feed = new \transit_realtime\FeedMessage();
        $feed->parse($fileData);

        return new self(collect($feed->getEntityList()));
    }

    public function isRealVehicle($vehicle_id, $trip_id)
    {
        return $this->entites->contains(function($entity) use ($vehicle_id, $trip_id) {
            return $entity->id == $vehicle_id && $entity->getVehicle()->getTrip()->trip_id == $trip_id;
        });
    }

    public function allPositions()
    {
        return $this->entites->map(function ($entity) {
            return [
                'id' => Uuid::uuid4()->toString(),
                'vehicle_id' => $entity->id,
                'timestamp' => Carbon::createFromTimestamp($entity->getVehicle()->timestamp)->toDateTimeString(),
                'trip_id' => $entity->getVehicle()->getTrip()->trip_id,
                'lat' => $entity->getVehicle()->getPosition()->getLatitude(),
                'lon' => $entity->getVehicle()->getPosition()->getLongitude()
            ];
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
