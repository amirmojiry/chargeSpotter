<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class GridCellController extends Controller
{
    public function index(Request $request)
    {
        $bboxString = $request->get('bbox');
        if (!$bboxString) {
            return response()->json(['error' => 'bbox parameter required'], 400);
        }

        $bbox = array_map('floatval', explode(',', $bboxString));
        if (count($bbox) !== 4) {
            return response()->json(['error' => 'bbox must have 4 values: minLng,minLat,maxLng,maxLat'], 400);
        }

        // Get weights from request or use defaults
        $weights = [
            'population' => (float) $request->get('w_pop', config('chargespotter.default_weights.population')),
            'poi' => (float) $request->get('w_poi', config('chargespotter.default_weights.poi')),
            'parking' => (float) $request->get('w_parking', config('chargespotter.default_weights.parking')),
            'traffic' => (float) $request->get('w_traffic', config('chargespotter.default_weights.traffic')),
        ];

        $scoringService = new ScoringService();
        $cells = $scoringService->getCellsInBbox($bbox, $weights);

        return response()->json(['cells' => $cells]);
    }
}
