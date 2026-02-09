<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\SubcategoryResource;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Subcategory::with('category')->orderBy('label');

        if ($slug = request('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }

        if ($categoryId = request('category_id')) {
            $query->where('category_id', $categoryId);
        }

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
