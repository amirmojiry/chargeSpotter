<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoiLocation extends Model
{
    protected $fillable = [
        'name',
        'category',
        'category_id',
        'lat',
        'lng',
        'grid_cell_id',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PoiCategory::class, 'category_id');
    }

    /**
     * Get the grid cell that owns this POI location.
     */
    public function gridCell(): BelongsTo
    {
        return $this->belongsTo(GridCell::class);
    }
}
