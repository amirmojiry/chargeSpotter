<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoiLocation extends Model
{
    protected $fillable = [
        'name',
        'category',
        'lat',
        'lng',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];
}
