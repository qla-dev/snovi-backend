<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use App\Support\LibraryPreferenceOrder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $preferredCategoryIds = LibraryPreferenceOrder::preferredCategoryIds($request);
            $preferredSubcategoryIds = LibraryPreferenceOrder::preferredSubcategoryIds($request);

            $query = Category::query()->with([
                'subcategories' => function ($subcategories) use ($preferredSubcategoryIds) {
                    $subcategories->reorder();
                    LibraryPreferenceOrder::applyIdPriority($subcategories, 'subcategories.id', $preferredSubcategoryIds);
                    LibraryPreferenceOrder::applyNullableSort($subcategories, 'subcategories.sort', 'subcategories.label');
                },
            ]);

            LibraryPreferenceOrder::applyIdPriority($query, 'categories.id', $preferredCategoryIds);
            LibraryPreferenceOrder::applyNullableSort($query, 'categories.sort', 'categories.label');

            $categories = $query->get();

            return CategoryResource::collection($categories);
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
    public function show(Request $request, Category $category)
    {
        $preferredSubcategoryIds = LibraryPreferenceOrder::preferredSubcategoryIds($request);

        $category->load([
            'subcategories' => function ($subcategories) use ($preferredSubcategoryIds) {
                $subcategories->reorder();
                LibraryPreferenceOrder::applyIdPriority($subcategories, 'subcategories.id', $preferredSubcategoryIds);
                LibraryPreferenceOrder::applyNullableSort($subcategories, 'subcategories.sort', 'subcategories.label');
            },
        ]);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        abort(405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        abort(405);
    }
}
