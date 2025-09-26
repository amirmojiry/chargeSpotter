<?php

namespace App\Console\Commands;

use App\Models\GridCell;
use App\Services\GridBuilder;
use Illuminate\Console\Command;

class BuildGrid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:build-grid 
                            {--bbox=21.5,63.05,21.75,63.15 : Bounding box as minLng,minLat,maxLng,maxLat}
                            {--cell=500 : Cell size in meters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build grid cells for a given bounding box';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bboxString = $this->option('bbox');
        $cellSize = (int) $this->option('cell');

        $bbox = array_map('floatval', explode(',', $bboxString));
        
        if (count($bbox) !== 4) {
            $this->error('Bbox must have 4 values: minLng,minLat,maxLng,maxLat');
            return 1;
        }

        $this->info("Building grid for bbox: " . implode(',', $bbox) . " with cell size: {$cellSize}m");

        $gridBuilder = new GridBuilder();
        $cells = $gridBuilder->buildGridFromBbox($bbox, $cellSize);

        $this->info("Generated " . count($cells) . " grid cells");

        // Clear existing grid cells
        GridCell::truncate();

        // Insert new grid cells
        foreach ($cells as $cell) {
            GridCell::create($cell);
        }

        $this->info("Grid cells saved to database");
        return 0;
    }
}
