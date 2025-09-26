<?php

namespace App\Services;

use App\Models\GridCell;
use App\Models\PoiLocation;
use App\Models\ParkingLocation;
use App\Models\PopulationCell;
use Illuminate\Support\Facades\DB;

class ImportService
{
    public function importPoiCsv(string $path): int
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // Skip header
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            PoiLocation::create([
                'name' => $row[0],
                'category' => $row[1],
                'lat' => (float) $row[2],
                'lng' => (float) $row[3],
            ]);
            $count++;
        }

        fclose($handle);
        return $count;
    }

    public function importParkingCsv(string $path): int
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // Skip header
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            ParkingLocation::create([
                'name' => $row[0],
                'capacity' => $row[1] ? (int) $row[1] : null,
                'lat' => (float) $row[2],
                'lng' => (float) $row[3],
            ]);
            $count++;
        }

        fclose($handle);
        return $count;
    }

    public function importPopulationCsv(string $path): int
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // Skip header
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            PopulationCell::create([
                'lat' => (float) $row[0],
                'lng' => (float) $row[1],
                'density' => (float) $row[2],
            ]);
            $count++;
        }

        fclose($handle);
        return $count;
    }

    public function assignLayerValuesToGrid(): void
    {
        // For each grid cell, compute counts/densities within radius/bbox
        $gridCells = GridCell::all();

        foreach ($gridCells as $cell) {
            $bbox = $cell->bbox_json;
            $cellLat = $cell->lat;
            $cellLng = $cell->lng;

            // POI density: count POIs within the cell bbox
            $poiCount = PoiLocation::whereBetween('lat', [$bbox[1], $bbox[3]])
                ->whereBetween('lng', [$bbox[0], $bbox[2]])
                ->count();

            // Parking: sum capacity within bbox
            $parkingCapacity = ParkingLocation::whereBetween('lat', [$bbox[1], $bbox[3]])
                ->whereBetween('lng', [$bbox[0], $bbox[2]])
                ->sum('capacity') ?? 0;

            // Population: average density of population points falling inside
            $populationDensity = PopulationCell::whereBetween('lat', [$bbox[1], $bbox[3]])
                ->whereBetween('lng', [$bbox[0], $bbox[2]])
                ->avg('density') ?? 0;

            // Traffic: for now, use POI count as proxy (can be enhanced later)
            $trafficProxy = $poiCount;

            $cell->update([
                'poi_z' => $poiCount,
                'parking_z' => $parkingCapacity,
                'population_z' => $populationDensity,
                'traffic_z' => $trafficProxy,
            ]);
        }
    }
}
