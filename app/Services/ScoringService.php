<?php

namespace App\Services;

use App\Models\GridCell;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    public function normalizeLayers(): void
    {
        // Get min and max values for each layer
        $stats = GridCell::selectRaw('
            MIN(population_z) as min_pop,
            MAX(population_z) as max_pop,
            MIN(poi_z) as min_poi,
            MAX(poi_z) as max_poi,
            MIN(parking_z) as min_parking,
            MAX(parking_z) as max_parking,
            MIN(COALESCE(traffic_z, 0)) as min_traffic,
            MAX(COALESCE(traffic_z, 0)) as max_traffic
        ')->first();

        // Normalize each layer to 0..1 using min-max normalization
        if ($stats->max_pop > $stats->min_pop) {
            GridCell::query()->update([
                'population_z' => DB::raw("(population_z - {$stats->min_pop}) / ({$stats->max_pop} - {$stats->min_pop})")
            ]);
        }

        if ($stats->max_poi > $stats->min_poi) {
            GridCell::query()->update([
                'poi_z' => DB::raw("(poi_z - {$stats->min_poi}) / ({$stats->max_poi} - {$stats->min_poi})")
            ]);
        }

        if ($stats->max_parking > $stats->min_parking) {
            GridCell::query()->update([
                'parking_z' => DB::raw("(parking_z - {$stats->min_parking}) / ({$stats->max_parking} - {$stats->min_parking})")
            ]);
        }

        if ($stats->max_traffic > $stats->min_traffic) {
            GridCell::query()->update([
                'traffic_z' => DB::raw("(COALESCE(traffic_z, 0) - {$stats->min_traffic}) / ({$stats->max_traffic} - {$stats->min_traffic})")
            ]);
        }
    }

    public function computeTotals(array $weights): void
    {
        $wPop = $weights['population'] ?? 0;
        $wPoi = $weights['poi'] ?? 0;
        $wParking = $weights['parking'] ?? 0;
        $wTraffic = $weights['traffic'] ?? 0;

        GridCell::query()->update([
            'total_score_cached' => DB::raw("
                ({$wPop} * population_z) + 
                ({$wPoi} * poi_z) + 
                ({$wParking} * parking_z) + 
                ({$wTraffic} * COALESCE(traffic_z, 0))
            ")
        ]);
    }

    public function topN(int $n, array $weights): array
    {
        $wPop = $weights['population'] ?? 0;
        $wPoi = $weights['poi'] ?? 0;
        $wParking = $weights['parking'] ?? 0;
        $wTraffic = $weights['traffic'] ?? 0;

        return GridCell::selectRaw("
            *,
            ({$wPop} * population_z) + 
            ({$wPoi} * poi_z) + 
            ({$wParking} * parking_z) + 
            ({$wTraffic} * COALESCE(traffic_z, 0)) as total_score
        ")
        ->orderBy('total_score', 'desc')
        ->limit($n)
        ->get()
        ->toArray();
    }

    public function getCellsInBbox(array $bbox, array $weights): array
    {
        $wPop = $weights['population'] ?? 0;
        $wPoi = $weights['poi'] ?? 0;
        $wParking = $weights['parking'] ?? 0;
        $wTraffic = $weights['traffic'] ?? 0;

        return GridCell::selectRaw("
            lat,
            lng,
            bbox_json as bbox,
            population_z,
            poi_z,
            parking_z,
            COALESCE(traffic_z, 0) as traffic_z,
            ({$wPop} * population_z) + 
            ({$wPoi} * poi_z) + 
            ({$wParking} * parking_z) + 
            ({$wTraffic} * COALESCE(traffic_z, 0)) as total_score
        ")
        ->whereRaw("
            lat BETWEEN ? AND ? AND 
            lng BETWEEN ? AND ?
        ", [$bbox[1], $bbox[3], $bbox[0], $bbox[2]])
        ->get()
        ->toArray();
    }
}
