<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulationCell extends Model
{
    protected $fillable = [
        'lat',
        'lng',
        'density',
        'grid_cell_id',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'density' => 'float',
    ];

    /**
     * Get the grid cell that owns this population cell.
     */
    public function gridCell(): BelongsTo
    {
        return $this->belongsTo(GridCell::class);
    }
}
