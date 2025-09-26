<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoiLocation;
use App\Models\PoiCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PoiLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PoiLocation::with('category');

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $poiLocations = $query->paginate(20);
        $categories = PoiCategory::orderBy('name')->get();

        return Inertia::render('Admin/PoiLocations/Index', [
            'poiLocations' => $poiLocations,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:poi_categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        PoiLocation::create($request->all());

        return redirect()->route('admin.poi-locations.index')
            ->with('success', 'POI location created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PoiLocation $poiLocation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:poi_categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $poiLocation->update($request->all());

        return redirect()->route('admin.poi-locations.index')
            ->with('success', 'POI location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PoiLocation $poiLocation)
    {
        $poiLocation->delete();

        return redirect()->route('admin.poi-locations.index')
            ->with('success', 'POI location deleted successfully.');
    }

    /**
     * Bulk delete multiple POI locations.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:poi_locations,id',
        ]);

        PoiLocation::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.poi-locations.index')
            ->with('success', count($request->ids) . ' POI locations deleted successfully.');
    }
}
