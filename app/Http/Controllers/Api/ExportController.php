<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function csv(Request $request)
    {
        $limit = (int) $request->get('limit', config('chargespotter.export.max_candidates'));

        // Get weights from request or use defaults
        $weights = [
            'population' => (float) $request->get('w_pop', config('chargespotter.default_weights.population')),
            'poi' => (float) $request->get('w_poi', config('chargespotter.default_weights.poi')),
            'parking' => (float) $request->get('w_parking', config('chargespotter.default_weights.parking')),
            'traffic' => (float) $request->get('w_traffic', config('chargespotter.default_weights.traffic')),
        ];

        $scoringService = new ScoringService();
        $candidates = $scoringService->topN($limit, $weights);

        $csv = "lat,lng,population_z,poi_z,parking_z,traffic_z,total_score\n";
        foreach ($candidates as $candidate) {
            $csv .= implode(',', [
                $candidate['lat'],
                $candidate['lng'],
                $candidate['population_z'],
                $candidate['poi_z'],
                $candidate['parking_z'],
                $candidate['traffic_z'],
                $candidate['total_score']
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="chargespotter_candidates.csv"'
        ]);
    }

    public function geojson(Request $request)
    {
        $limit = (int) $request->get('limit', config('chargespotter.export.max_candidates'));

        // Get weights from request or use defaults
        $weights = [
            'population' => (float) $request->get('w_pop', config('chargespotter.default_weights.population')),
            'poi' => (float) $request->get('w_poi', config('chargespotter.default_weights.poi')),
            'parking' => (float) $request->get('w_parking', config('chargespotter.default_weights.parking')),
            'traffic' => (float) $request->get('w_traffic', config('chargespotter.default_weights.traffic')),
        ];

        $scoringService = new ScoringService();
        $candidates = $scoringService->topN($limit, $weights);

        $features = [];
        foreach ($candidates as $candidate) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$candidate['lng'], $candidate['lat']]
                ],
                'properties' => [
                    'population_z' => $candidate['population_z'],
                    'poi_z' => $candidate['poi_z'],
                    'parking_z' => $candidate['parking_z'],
                    'traffic_z' => $candidate['traffic_z'],
                    'total_score' => $candidate['total_score']
                ]
            ];
        }

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];

        return response()->json($geojson);
    }
}
