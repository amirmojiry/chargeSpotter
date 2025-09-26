<?php

use App\Http\Controllers\Admin\PoiCategoryController;
use App\Http\Controllers\Admin\PoiLocationController;
use App\Http\Controllers\Admin\ParkingLocationController;
use App\Http\Controllers\Admin\PopulationCellController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
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
