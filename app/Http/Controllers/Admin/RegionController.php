<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Region::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $regions = $query->withCount('gridCells')->paginate(20);

        return Inertia::render('Admin/Regions/Index', [
            'regions' => $regions,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Regions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area_json' => 'required|array',
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
        ]);

        $region = Region::create($request->all());

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        $region->load('gridCells');

        return Inertia::render('Admin/Regions/Show', [
            'region' => $region,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * DISABLED: Edit functionality temporarily disabled
     */
    public function edit(Region $region)
    {
        // DISABLED: Edit functionality temporarily disabled
        return redirect()->route('admin.regions.index')
            ->with('error', 'Edit functionality is currently disabled.');
        
        /* ORIGINAL CODE - UNCOMMENT TO RE-ENABLE:
        return Inertia::render('Admin/Regions/Edit', [
            'region' => $region,
        ]);
        */
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * DISABLED: Update functionality temporarily disabled
     */
    public function update(Request $request, Region $region)
    {
        // DISABLED: Update functionality temporarily disabled
        return redirect()->route('admin.regions.index')
            ->with('error', 'Update functionality is currently disabled.');
        
        /* ORIGINAL CODE - UNCOMMENT TO RE-ENABLE:
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area_json' => 'required|array',
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
        ]);

        $region->update($request->all());

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region updated successfully.');
        */
    }

    /**
     * Remove the specified resource from storage.
     * DISABLED: Delete functionality temporarily disabled
     */
    public function destroy(Region $region)
    {
        // DISABLED: Delete functionality temporarily disabled
        return redirect()->route('admin.regions.index')
            ->with('error', 'Delete functionality is currently disabled.');
        
        /* ORIGINAL CODE - UNCOMMENT TO RE-ENABLE:
        $region->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region deleted successfully.');
        */
    }

    /**
     * Bulk delete multiple regions.
     * DISABLED: Bulk delete functionality temporarily disabled
     */
    public function bulkDestroy(Request $request)
    {
        // DISABLED: Bulk delete functionality temporarily disabled
        return redirect()->route('admin.regions.index')
            ->with('error', 'Bulk delete functionality is currently disabled.');
        
        /* ORIGINAL CODE - UNCOMMENT TO RE-ENABLE:
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:regions,id',
        ]);

        Region::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', count($request->ids) . ' regions deleted successfully.');
        */
    }
}
