<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\GridCell;
use Illuminate\Console\Command;

class RegenerateGridCells extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'region:regenerate-grid {--region-id= : Specific region ID to regenerate} {--cell-size=0.005 : Grid cell size in degrees}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate grid cells for regions to ensure complete coverage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $regionId = $this->option('region-id');
        $cellSize = (float) $this->option('cell-size');

        if ($regionId) {
            $region = Region::find($regionId);
            if (!$region) {
                $this->error("Region with ID {$regionId} not found.");
                return Command::FAILURE;
            }
            $regions = collect([$region]);
        } else {
            $regions = Region::all();
        }

        if ($regions->isEmpty()) {
            $this->info('No regions found.');
            return Command::SUCCESS;
        }

        $this->info("Regenerating grid cells for {$regions->count()} region(s) with cell size {$cellSize}...");

        foreach ($regions as $region) {
            $this->regenerateGridCellsForRegion($region, $cellSize);
        }

        $this->info('Grid cell regeneration completed.');
        return Command::SUCCESS;
    }

    private function regenerateGridCellsForRegion(Region $region, float $cellSize): void
    {
        $this->info("Processing region: {$region->name} (ID: {$region->id})");

        // Delete existing grid cells
        $deletedCount = GridCell::where('region_id', $region->id)->delete();
        $this->info("  Deleted {$deletedCount} existing grid cells");

        // Calculate region bounds
        $areaJson = $region->area_json;
        if (!$areaJson || count($areaJson) < 3) {
            $this->warn("  Skipping region {$region->name}: Invalid area geometry");
            return;
        }

        $regionBounds = $this->calculateRegionBounds($areaJson);
        $minLat = $regionBounds['minLat'];
        $maxLat = $regionBounds['maxLat'];
        $minLng = $regionBounds['minLng'];
        $maxLng = $regionBounds['maxLng'];

        $gridCellsCreated = 0;

        // Generate grid cells that cover the entire region
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

                // Check if the grid cell intersects with the region polygon
                if ($this->gridCellIntersectsRegion($bbox, $areaJson)) {
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
        }

        $this->info("  Created {$gridCellsCreated} new grid cells");
    }

    /**
     * Calculate the bounding box of a region polygon
     */
    private function calculateRegionBounds(array $areaJson): array
    {
        $minLat = PHP_FLOAT_MAX;
        $maxLat = PHP_FLOAT_MIN;
        $minLng = PHP_FLOAT_MAX;
        $maxLng = PHP_FLOAT_MIN;

        foreach ($areaJson as $point) {
            $minLat = min($minLat, $point['lat']);
            $maxLat = max($maxLat, $point['lat']);
            $minLng = min($minLng, $point['lng']);
            $maxLng = max($maxLng, $point['lng']);
        }

        return [
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng
        ];
    }

    /**
     * Check if a grid cell intersects with the region polygon
     * Using a simple point-in-polygon test for the cell center
     */
    private function gridCellIntersectsRegion(array $bbox, array $areaJson): bool
    {
        $cellCenterLat = ($bbox[1] + $bbox[3]) / 2; // Average of min/max lat
        $cellCenterLng = ($bbox[0] + $bbox[2]) / 2; // Average of min/max lng

        return $this->pointInPolygon($cellCenterLat, $cellCenterLng, $areaJson);
    }

    /**
     * Point-in-polygon test using ray casting algorithm
     */
    private function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            if ((($yi > $lat) !== ($yj > $lat)) && 
                ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }

    /**
     * Generate a random score between 0 and 1
     */
    private function generateRandomScore(): float
    {
        return round(mt_rand(0, 100) / 100, 3);
    }
}
