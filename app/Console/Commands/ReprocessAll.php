<?php

namespace App\Console\Commands;

use App\Model\Batch;
use App\VehiclePositions;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use transit_realtime\TripUpdate\StopTimeUpdate;

class ReprocessAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reprocess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Factory
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Factory $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->writeln("<info>Writing vehicle positions...</info>");
        collect($this->filesystem->disk('events')->allFiles())
            ->filter(function ($name) {
                return str_contains($name, 'positions');
            })
            ->each(function ($fileName) {
                $positions = VehiclePositions::fromData($this->filesystem->disk('events')->get($fileName))
                    ->allPositions();

                \DB::table('vehicle_positions')->insert($positions->all());
            });

        $foundTripIds = new Collection();

        collect($this->filesystem->disk('events')->allFiles())
            ->filter(function ($name) {
                return str_contains($name, 'updates');
            })
            ->sort()
            ->reverse()
            ->each(function ($tripFileName) use ($foundTripIds) {
                $timestamp = Carbon::createFromTimestamp(explode('-', $tripFileName)[0]);

                $feed = new \transit_realtime\FeedMessage();
                $feed->parse($this->filesystem->disk('events')->get($tripFileName));

                $batch = Batch::init();

                foreach($feed->getEntityList() as $entity) {

                    if ($entity->hasTripUpdate()) {
                        $vehicle_id = $entity->getTripUpdate()->getVehicle()->getLabel();
                        $route = $entity->getTripUpdate()->getTrip()->route_id;

                        $uniqueId = $entity->id . '-' . $vehicle_id . "-" . $timestamp->toDateString();

                        // We only want to process a trip_id once.
                        if ($foundTripIds->contains($uniqueId)) {
                            continue;
                        }

                        // Add it to the found trip Ids
                        $foundTripIds->push($uniqueId);

                        //TODO removed the check if this is currently running on a real vehicle

                        $constant_data = [
                            'batch_id' => $batch->id,
                            'route' => $route,
                            'vehicle_id' => $vehicle_id,
                            'trip_id' => $entity->id,
                            'created_at' => $timestamp->toDateTimeString(),
                            'updated_at' => $timestamp->toDateTimeString(),
                        ];

                        $delays = collect($entity->getTripUpdate()->getStopTimeUpdate())
                            ->map(function (StopTimeUpdate $stopTimeUpdate) use ($constant_data) {
                                $result = [
                                    'id' => Uuid::uuid4()->toString(),
                                    'stop_id' => $stopTimeUpdate->getStopId(),
                                    'depart_at' => null,
                                    'depart_delay' => null,
                                    'arrival_at' => null,
                                    'arrival_delay' => null,
                                ];

                                if ($stopTimeUpdate->hasDeparture()) {

                                    $result['depart_at'] = Carbon::createFromTimestamp($stopTimeUpdate->getDeparture()->time)->toDateTimeString();
                                    $result['depart_delay'] = $stopTimeUpdate->getDeparture()->delay ?? null;
                                }

                                if ($stopTimeUpdate->hasArrival()) {
                                    $result['arrival_at'] = Carbon::createFromTimestamp($stopTimeUpdate->getArrival()->time)->toDateTimeString();
                                    $result['arrival_delay'] = $stopTimeUpdate->getArrival()->delay ?? null;
                                }

                                return array_merge($constant_data, $result);
                        });

                        \DB::table('bus_times')->insert($delays->toArray());
                        $this->output->writeln("<info>Inserted {$delays->count()} records!</info>");
                    }


                }

            });

        $this->output->writeln('done!');

    }
}
