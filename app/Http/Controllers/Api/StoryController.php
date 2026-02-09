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
        $query = Story::with(['category', 'subcategory'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

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

        if ($subcategory = $request->query('subcategory')) {
            $query->whereHas('subcategory', fn ($q) => $q->where('slug', $subcategory));
        }

        if ($request->boolean('favorite')) {
            $query->where('is_favorite', true);
        }

        if ($request->boolean('locked')) {
            $query->where('locked', true);
        } elseif ($request->boolean('unlocked')) {
            $query->where('locked', false);
        }

        $limit = min(max($request->integer('limit', 100), 1), 200);

        $stories = $query->limit($limit)->get();

        return StoryResource::collection($stories);
    }

    /**
     * Return latest published stories (with image and audio) capped at 10.
     */
    public function recentPublished(Request $request)
    {
        $limit = min(max($request->integer('limit', 10), 1), 50);
        $stories = Story::with(['category', 'subcategory'])
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
        $story->load(['category', 'subcategory']);

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
