<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\GridCell;
use Illuminate\Console\Command;

class CreateSampleRegion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'region:create-sample {--name=Vaasa : The name of the region}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sample region with grid cells for Vaasa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name');
        
        $this->info("Creating sample region: {$name}");

        // Vaasa coordinates (approximate city center and boundaries)
        $centerLat = 63.0960;
        $centerLng = 21.6158;
        
        // Define a rectangular area around Vaasa city center
        $minLat = 63.05;
        $maxLat = 63.15;
        $minLng = 21.55;
        $maxLng = 21.68;

        // Create area polygon (simple rectangle for now)
        $areaJson = [
            ['lat' => $minLat, 'lng' => $minLng],
            ['lat' => $maxLat, 'lng' => $minLng],
            ['lat' => $maxLat, 'lng' => $maxLng],
            ['lat' => $minLat, 'lng' => $maxLng],
            ['lat' => $minLat, 'lng' => $minLng] // Close the polygon
        ];

        // Create the region
        $region = Region::create([
            'name' => $name,
            'description' => "Sample region for {$name} with grid cells for EV charging analysis",
            'area_json' => $areaJson,
            'center_lat' => $centerLat,
            'center_lng' => $centerLng,
        ]);

        $this->info("Region created with ID: {$region->id}");

        // Create grid cells
        $this->info("Creating grid cells...");
        
        $cellSize = 0.005; // Approximately 500m at this latitude
        $gridCellsCreated = 0;

        for ($lat = $minLat; $lat < $maxLat; $lat += $cellSize) {
            for ($lng = $minLng; $lng < $maxLng; $lng += $cellSize) {
                $cellCenterLat = $lat + ($cellSize / 2);
                $cellCenterLng = $lng + ($cellSize / 2);
                
                $bbox = [
                    $lng, // minLng
                    $lat, // minLat
                    $lng + $cellSize, // maxLng
                    $lat + $cellSize  // maxLat
                ];

                GridCell::create([
                    'region_id' => $region->id,
                    'lat' => $cellCenterLat,
                    'lng' => $cellCenterLng,
                    'bbox_json' => $bbox,
                    'population_z' => $this->generateRandomScore(),
                    'poi_z' => $this->generateRandomScore(),
                    'parking_z' => $this->generateRandomScore(),
                    'traffic_z' => $this->generateRandomScore(),
                ]);

                $gridCellsCreated++;
            }
        }

        $this->info("Created {$gridCellsCreated} grid cells for region '{$name}'");
        $this->info("Region center: {$centerLat}, {$centerLng}");
        $this->info("Area bounds: {$minLat} to {$maxLat} (lat), {$minLng} to {$maxLng} (lng)");
        
        return Command::SUCCESS;
    }

    /**
     * Generate a random score between 0 and 1
     */
    private function generateRandomScore(): float
    {
        return round(mt_rand(0, 100) / 100, 3);
    }
}
