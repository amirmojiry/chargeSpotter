<?php

namespace App\Services;

class GridBuilder
{
    public function buildGridFromBbox(array $bbox, int $cellSizeM): array
    {
        // bbox = [minLng, minLat, maxLng, maxLat]
        // Convert meters to degrees (simple equirectangular approximation)
        $latDegreePerMeter = 1 / 111320; // Approximate meters per degree of latitude
        $lngDegreePerMeter = 1 / (111320 * cos(deg2rad(($bbox[1] + $bbox[3]) / 2))); // Adjust for longitude

        $cellSizeLat = $cellSizeM * $latDegreePerMeter;
        $cellSizeLng = $cellSizeM * $lngDegreePerMeter;

        $cells = [];
        $minLng = $bbox[0];
        $minLat = $bbox[1];
        $maxLng = $bbox[2];
        $maxLat = $bbox[3];

        // Iterate through the grid
        for ($lat = $minLat; $lat < $maxLat; $lat += $cellSizeLat) {
            for ($lng = $minLng; $lng < $maxLng; $lng += $cellSizeLng) {
                $cellMaxLat = min($lat + $cellSizeLat, $maxLat);
                $cellMaxLng = min($lng + $cellSizeLng, $maxLng);
                
                $cellCenterLat = ($lat + $cellMaxLat) / 2;
                $cellCenterLng = ($lng + $cellMaxLng) / 2;
                
                $cells[] = [
                    'lat' => $cellCenterLat,
                    'lng' => $cellCenterLng,
                    'bbox_json' => [$lng, $lat, $cellMaxLng, $cellMaxLat]
                ];
            }
        }

        return $cells;
    }
}
