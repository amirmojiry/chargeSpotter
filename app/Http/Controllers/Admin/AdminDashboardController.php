<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'defaultWeights' => config('chargespotter.default_weights'),
            'defaultBbox' => [21.6, 63.09, 21.65, 63.13], // Vaasa example bbox
        ]);
    }
}
