<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoiCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PoiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PoiCategory::query();

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

        $categories = $query->withCount('poiLocations')->paginate(20);

        return Inertia::render('Admin/PoiCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:poi_categories',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        PoiCategory::create($request->all());

        return redirect()->route('admin.poi-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PoiCategory $poiCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:poi_categories,name,' . $poiCategory->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $poiCategory->update($request->all());

        return redirect()->route('admin.poi-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PoiCategory $poiCategory)
    {
        if ($poiCategory->poiLocations()->count() > 0) {
            return redirect()->route('admin.poi-categories.index')
                ->with('error', 'Cannot delete category with associated POI locations.');
        }

        $poiCategory->delete();

        return redirect()->route('admin.poi-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
