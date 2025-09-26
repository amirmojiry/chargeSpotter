<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkingLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ParkingLocation::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by capacity range
        if ($request->filled('min_capacity')) {
            $query->where('capacity', '>=', $request->get('min_capacity'));
        }
        if ($request->filled('max_capacity')) {
            $query->where('capacity', '<=', $request->get('max_capacity'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $parkingLocations = $query->paginate(20);

        return Inertia::render('Admin/ParkingLocations/Index', [
            'parkingLocations' => $parkingLocations,
            'filters' => $request->only(['search', 'min_capacity', 'max_capacity', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        ParkingLocation::create($request->all());

        return redirect()->route('admin.parking-locations.index')
            ->with('success', 'Parking location created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParkingLocation $parkingLocation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $parkingLocation->update($request->all());

        return redirect()->route('admin.parking-locations.index')
            ->with('success', 'Parking location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParkingLocation $parkingLocation)
    {
        $parkingLocation->delete();

        return redirect()->route('admin.parking-locations.index')
            ->with('success', 'Parking location deleted successfully.');
    }

    /**
     * Bulk delete multiple parking locations.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:parking_locations,id',
        ]);

        ParkingLocation::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.parking-locations.index')
            ->with('success', count($request->ids) . ' parking locations deleted successfully.');
    }
}
