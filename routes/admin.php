<?php

use App\Http\Controllers\Admin\PoiCategoryController;
use App\Http\Controllers\Admin\PoiLocationController;
use App\Http\Controllers\Admin\ParkingLocationController;
use App\Http\Controllers\Admin\PopulationCellController;
use App\Http\Controllers\Admin\RegionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Regions
    Route::resource('regions', RegionController::class);
    Route::delete('regions/bulk', [RegionController::class, 'bulkDestroy'])->name('regions.bulk-destroy');
    
    // POI Categories
    Route::resource('poi-categories', PoiCategoryController::class);
    
    // POI Locations
    Route::resource('poi-locations', PoiLocationController::class);
    Route::delete('poi-locations/bulk', [PoiLocationController::class, 'bulkDestroy'])->name('poi-locations.bulk-destroy');
    
    // Parking Locations
    Route::resource('parking-locations', ParkingLocationController::class);
    Route::delete('parking-locations/bulk', [ParkingLocationController::class, 'bulkDestroy'])->name('parking-locations.bulk-destroy');
    
    // Population Cells
    Route::resource('population-cells', PopulationCellController::class);
    Route::delete('population-cells/bulk', [PopulationCellController::class, 'bulkDestroy'])->name('population-cells.bulk-destroy');
});
