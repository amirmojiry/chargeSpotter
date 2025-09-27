<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'name',
        'description',
        'area_json',
        'center_lat',
        'center_lng',
    ];

    protected $casts = [
        'area_json' => 'array',
        'center_lat' => 'float',
        'center_lng' => 'float',
    ];

    /**
     * Get the grid cells for this region.
     */
    public function gridCells(): HasMany
    {
        return $this->hasMany(GridCell::class);
    }
}
