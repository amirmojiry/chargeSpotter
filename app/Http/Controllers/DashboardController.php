<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'defaultWeights' => config('chargespotter.default_weights'),
            'defaultBbox' => [21.6, 63.09, 21.65, 63.13], // Vaasa example bbox
        ]);
    }
}
