<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoiCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function poiLocations(): HasMany
    {
        return $this->hasMany(PoiLocation::class, 'category_id');
    }
}
