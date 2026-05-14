<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use App\Http\Resources\StoryResource;
use Illuminate\Database\Eloquent\Builder;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Story::with(['category', 'subcategory', 'music']);

        $this->applySort($query, $request);

        if (!$request->boolean('include_dummy')) {
            $query->where('is_dummy', false);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('narrator', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($subcategory = $request->input('subcategory')) {
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

        $stories = $query
            ->forPage($this->pageNo($request), $this->limit($request))
            ->get();

        return StoryResource::collection($stories);
    }

    /**
     * Return published stories with image and audio.
     */
    public function recentPublished(Request $request)
    {
        $limit = $this->limit($request, 50);
        $query = Story::with(['category', 'subcategory', 'music'])
            ->where('is_dummy', false)
            ->whereNotNull('image_url')
            ->where(function ($q) {
                $q->whereNotNull('audio_url')
                    ->orWhereNotNull('audio_path');
            });

        $this->applySort($query, $request);

        $stories = $query
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

    private function limit(Request $request, int $default = 10): int
    {
        return min(max($request->integer('limit', $default), 1), 1000);
    }

    private function pageNo(Request $request): int
    {
        return max($request->integer('page_no', 1), 1);
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = strtolower(trim((string) $request->input('sort', 'asc')));

        if ($sort === 'desc') {
            $this->applyPublishedSort($query, 'desc');

            return;
        }

        if ($sort === 'abc') {
            $this->applyAlphabeticalSort($query);

            return;
        }

        $this->applyPublishedSort($query, 'asc');
    }

    private function applyPublishedSort(Builder $query, string $direction): void
    {
        $query->orderBy('id', $direction);
    }

    private function applyAlphabeticalSort(Builder $query): void
    {
        $query->orderByRaw('LOWER(title) asc')
            ->orderBy('id');
    }
}
