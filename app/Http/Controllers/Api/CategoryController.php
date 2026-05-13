<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\LibraryPreferenceOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->categoryQuery($request);
        $this->applySearch($query, $request->query('q'));

        $categories = $query->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Search categories and their subcategories.
     */
    public function search(Request $request)
    {
        return $this->index($request);
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

    private function categoryQuery(Request $request): Builder
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

        return $query;
    }

    private function applySearch(Builder $query, mixed $search): void
    {
        $term = trim((string) $search);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $categoryQuery) use ($term) {
            $categoryQuery
                ->where('categories.label', 'like', "%{$term}%")
                ->orWhere('categories.slug', 'like', "%{$term}%")
                ->orWhere('categories.description', 'like', "%{$term}%")
                ->orWhereHas('subcategories', function (Builder $subcategoryQuery) use ($term) {
                    $subcategoryQuery
                        ->where('subcategories.label', 'like', "%{$term}%")
                        ->orWhere('subcategories.slug', 'like', "%{$term}%");
                });
        });
    }

}
