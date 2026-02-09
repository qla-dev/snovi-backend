<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::with('category')
            ->orderBy('category_id')
            ->orderBy('label')
            ->get();
        $categories = Category::orderBy('label')->get();

        return view('admin.subcategories.index', compact('subcategories', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'slug' => ['required', 'string', 'max:80'],
            'label' => ['required', 'string', 'max:180'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $exists = Subcategory::where('category_id', $data['category_id'])
            ->where('slug', $data['slug'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Šifra mora biti jedinstvena unutar kategorije.'])->withInput();
        }

        Subcategory::create($data);

        return redirect()->route('admin.subcategories.index')->with('status', 'Potkategorija je dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        return redirect()->route('admin.subcategories.edit', $subcategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'slug' => ['required', 'string', 'max:80'],
            'label' => ['required', 'string', 'max:180'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $exists = Subcategory::where('category_id', $data['category_id'])
            ->where('slug', $data['slug'])
            ->where('id', '!=', $subcategory->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Šifra mora biti jedinstvena unutar kategorije.'])->withInput();
        }

        $subcategory->update($data);

        return redirect()->route('admin.subcategories.index')->with('status', 'Potkategorija je ažurirana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')->with('status', 'Potkategorija je obrisana.');
    }
}
