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
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PoiCategory::class, 'category_id');
    }
}
