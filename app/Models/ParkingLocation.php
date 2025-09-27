<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingLocation extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'lat',
        'lng',
        'grid_cell_id',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Get the grid cell that owns this parking location.
     */
    public function gridCell(): BelongsTo
    {
        return $this->belongsTo(GridCell::class);
    }
}
