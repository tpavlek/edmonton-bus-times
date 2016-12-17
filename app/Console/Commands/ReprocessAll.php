<?php

namespace App\Console\Commands;

use App\Model\Batch;
use App\Model\DailyTrips;
use App\Model\SequencedStop;
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
        $foundTripIds = new Collection();

        $this->output->writeln("<info>Processing events on disk...</info>");

        $events = collect($this->filesystem->disk('events')->allFiles())
            ->filter(function ($name) {
                return str_contains($name, 'updates');
            })
            ->sort()
            ->reverse();

        $this->output->writeln("<info>Beginning insert...</info>");

        $dailyTrips = new DailyTrips();

        $i = 0;

        $readyToStart = false;

        $events->each(function ($tripFileName) use ($foundTripIds, $dailyTrips, &$i, &$readyToStart) {
                $timestamp = Carbon::createFromTimestamp(explode('-', $tripFileName)[0]);

                if ($timestamp->hour == 4) {
                    $readyToStart = true;
                    if ($dailyTrips->isEmpty()) {
                        return;
                    }

                    $recorded = $dailyTrips->saveDay();

                    $this->output->writeln("Writing $recorded daily trips");

                    return;
                }

                if (!$readyToStart) {
                    return;
                }

                $feed = new \transit_realtime\FeedMessage();
                $feed->parse($this->filesystem->disk('events')->get($tripFileName));

                $filenameParts = collect(explode('-', $tripFileName));

                $uuid = $filenameParts
                    ->slice(1, $filenameParts->count() - 2)
                    ->implode('-');

                $batch = Batch::query()->firstOrCreate([ 'id' => $uuid ]);

                foreach($feed->getEntityList() as $entity) {

                    if ($entity->hasTripUpdate()) {
                        $vehicle_id = $entity->getTripUpdate()->getVehicle()->getLabel();
                        $route = $entity->getTripUpdate()->getTrip()->route_id;

                        if ($route != 4) {
                            continue;
                        }

                        $sequence = $dailyTrips->init($entity->id);

                        //TODO removed the check if this is currently running on a real vehicle

                        $constant_data = [
                            'batch_id' => $batch->id,
                            'route' => $route,
                            'vehicle_id' => $vehicle_id,
                            'trip_id' => $entity->id,
                            'created_at' => $timestamp->toDateTimeString(),
                            'updated_at' => $timestamp->toDateTimeString(),
                        ];

                        collect($entity->getTripUpdate()->getStopTimeUpdate())
                            ->each(function (StopTimeUpdate $stopTimeUpdate) use ($sequence, $timestamp, $constant_data) {

                                $sequence->record($stopTimeUpdate, $timestamp, $constant_data);

                            });
                    }
                }

                if ($i % 100 == 0) {
                    $eggs = "true";
                }

                $i++;

            });

        $this->output->writeln('done!');

    }
}
