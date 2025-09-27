<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLocation extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'lat',
        'lng',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
    ];
}
