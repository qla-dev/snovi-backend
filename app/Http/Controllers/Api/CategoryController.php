<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $categories = Category::with('subcategories')->orderBy('label')->get();

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
    public function show(Category $category)
    {
        $category->load('subcategories');

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
