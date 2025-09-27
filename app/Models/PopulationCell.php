<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopulationCell extends Model
{
    protected $fillable = [
        'lat',
        'lng',
        'density',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'density' => 'float',
    ];
}
