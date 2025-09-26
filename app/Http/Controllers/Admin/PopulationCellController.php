<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopulationCell;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PopulationCellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PopulationCell::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('lat', 'like', "%{$search}%")
                  ->orWhere('lng', 'like', "%{$search}%");
            });
        }

        // Filter by density range
        if ($request->filled('min_density')) {
            $query->where('density', '>=', $request->get('min_density'));
        }
        if ($request->filled('max_density')) {
            $query->where('density', '<=', $request->get('max_density'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'density');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $populationCells = $query->paginate(20);

        return Inertia::render('Admin/PopulationCells/Index', [
            'populationCells' => $populationCells,
            'filters' => $request->only(['search', 'min_density', 'max_density', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'density' => 'required|numeric|min:0',
        ]);

        PopulationCell::create($request->all());

        return redirect()->route('admin.population-cells.index')
            ->with('success', 'Population cell created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PopulationCell $populationCell)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'density' => 'required|numeric|min:0',
        ]);

        $populationCell->update($request->all());

        return redirect()->route('admin.population-cells.index')
            ->with('success', 'Population cell updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PopulationCell $populationCell)
    {
        $populationCell->delete();

        return redirect()->route('admin.population-cells.index')
            ->with('success', 'Population cell deleted successfully.');
    }

    /**
     * Bulk delete multiple population cells.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:population_cells,id',
        ]);

        PopulationCell::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.population-cells.index')
            ->with('success', count($request->ids) . ' population cells deleted successfully.');
    }
}
