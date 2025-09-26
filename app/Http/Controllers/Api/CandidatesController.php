<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class CandidatesController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 10);

        // Get weights from request or use defaults
        $weights = [
            'population' => (float) $request->get('w_pop', config('chargespotter.default_weights.population')),
            'poi' => (float) $request->get('w_poi', config('chargespotter.default_weights.poi')),
            'parking' => (float) $request->get('w_parking', config('chargespotter.default_weights.parking')),
            'traffic' => (float) $request->get('w_traffic', config('chargespotter.default_weights.traffic')),
        ];

        $scoringService = new ScoringService();
        $candidates = $scoringService->topN($limit, $weights);

        // Add reason strings based on details_json
        foreach ($candidates as &$candidate) {
            $candidate['reason'] = $this->generateReason($candidate, $weights);
        }

        return response()->json(['candidates' => $candidates]);
    }

    private function generateReason(array $candidate, array $weights): string
    {
        $reasons = [];
        
        if ($candidate['population_z'] > 0.7) {
            $reasons[] = __('chargespotter.reasons.high_population_density');
        }
        if ($candidate['poi_z'] > 0.7) {
            $reasons[] = __('chargespotter.reasons.many_points_of_interest');
        }
        if ($candidate['parking_z'] > 0.7) {
            $reasons[] = __('chargespotter.reasons.good_parking_availability');
        }
        if ($candidate['traffic_z'] > 0.7) {
            $reasons[] = __('chargespotter.reasons.high_traffic_flow');
        }

        return empty($reasons) ? __('chargespotter.balanced_location') : implode(', ', $reasons);
    }
}
