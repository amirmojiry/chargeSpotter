<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GridCell extends Model
{
    protected $fillable = [
        'lat',
        'lng',
        'bbox_json',
        'population_z',
        'poi_z',
        'parking_z',
        'traffic_z',
        'total_score_cached',
        'details_json',
    ];

    protected $casts = [
        'bbox_json' => 'array',
        'details_json' => 'array',
        'population_z' => 'float',
        'poi_z' => 'float',
        'parking_z' => 'float',
        'traffic_z' => 'float',
        'total_score_cached' => 'float',
    ];
}
