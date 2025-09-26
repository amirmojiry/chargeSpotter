<?php

use App\Http\Controllers\Api\GridCellController;
use App\Http\Controllers\Api\CandidatesController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/grid-cells', [GridCellController::class, 'index']);
Route::get('/candidates', [CandidatesController::class, 'index']);
Route::get('/export/csv', [ExportController::class, 'csv']);
Route::get('/export/geojson', [ExportController::class, 'geojson']);
