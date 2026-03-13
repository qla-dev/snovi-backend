<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use App\Http\Resources\StoryResource;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Story::with(['category', 'subcategory', 'music'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if (!$request->boolean('include_dummy')) {
            $query->where('is_dummy', false);
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('narrator', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($subcategory = $request->query('subcategory')) {
            $query->whereHas('subcategory', fn ($q) => $q->where('slug', $subcategory));
        }

        if ($subcategoryId = $request->integer('subcategory_id')) {
            $query->where('subcategory_id', $subcategoryId);
        }

        if ($request->boolean('favorite')) {
            $query->where('is_favorite', true);
        }

        if ($request->boolean('locked')) {
            $query->where('locked', true);
        } elseif ($request->boolean('unlocked')) {
            $query->where('locked', false);
        }

        $limit = min(max($request->integer('limit', 100), 1), 1000);

        $stories = $query->limit($limit)->get();

        return StoryResource::collection($stories);
    }

    /**
     * Return published stories with image and audio.
     */
    public function recentPublished(Request $request)
    {
        $limit = min(max($request->integer('limit', 50), 1), 1000);
        $stories = Story::with(['category', 'subcategory', 'music'])
            ->where('is_dummy', false)
            ->whereNotNull('image_url')
            ->where(function ($q) {
                $q->whereNotNull('audio_url')
                    ->orWhereNotNull('audio_path');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return StoryResource::collection($stories);
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
    public function show(Story $story)
    {
        $story->load(['category', 'subcategory', 'music']);

        return new StoryResource($story);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Story $story)
    {
        abort(405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Story $story)
    {
        abort(405);
    }
}
