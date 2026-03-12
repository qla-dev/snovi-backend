<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\SubcategoryResource;
use App\Support\LibraryPreferenceOrder;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subcategory::with('category');
        $preferredCategoryIds = LibraryPreferenceOrder::preferredCategoryIds($request);
        $preferredSubcategoryIds = LibraryPreferenceOrder::preferredSubcategoryIds($request);

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        LibraryPreferenceOrder::applyIdPriority($query, 'subcategories.category_id', $preferredCategoryIds);
        LibraryPreferenceOrder::applyIdPriority($query, 'subcategories.id', $preferredSubcategoryIds);
        LibraryPreferenceOrder::applyNullableSort($query, 'subcategories.sort', 'subcategories.label');

        return SubcategoryResource::collection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(405);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');

        return new SubcategoryResource($subcategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        abort(405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $subcategory)
    {
        abort(405);
    }
}
