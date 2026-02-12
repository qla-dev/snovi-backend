<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use getID3;

class StoryController extends Controller
{
    private array $effectKeys = ['ocean', 'rain', 'fire', 'leaves', 'river', 'birds', 'fan', 'snow', 'train', 'crickets'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stories = Story::with(['category', 'subcategory'])
            ->orderBy('is_dummy') // real content first
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.stories.index', compact('stories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('label')->get();
        $subcategories = Subcategory::orderBy('label')->get();

        return view('admin.stories.create', [
            'story' => new Story([
                'effects' => array_fill_keys($this->effectKeys, 0),
            ]),
            'categories' => $categories,
            'subcategories' => $subcategories,
            'effectKeys' => $this->effectKeys,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        Log::info('story.store.start', [
            'slug' => $data['slug'] ?? null,
            'title' => $data['title'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title'] . '-' . Str::random(4));

        if ($request->hasFile('audio_upload')) {
            $path = $request->file('audio_upload')->store('audio', 'public');
            $data['audio_path'] = $path;
            $data['audio_url'] = Storage::url($path);
            $this->enrichAudioMetadata($path, $data);
        }

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('images', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $data['effects'] = $this->extractEffects($request);
        $data['is_dummy'] = $request->boolean('is_dummy');
        $data['locked'] = $request->boolean('locked');
        $data['is_favorite'] = $request->boolean('is_favorite');

        $story = Story::create($data);

        Log::info('story.store.done', ['story_id' => $story->id, 'slug' => $story->slug]);

        return redirect()->route('admin.stories.index')->with('status', 'Priča je dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Story $story)
    {
        return redirect()->route('admin.stories.edit', $story);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Story $story)
    {
        $categories = Category::orderBy('label')->get();
        $subcategories = Subcategory::orderBy('label')->get();

        $story->effects = array_merge(
            array_fill_keys($this->effectKeys, 0),
            $story->effects ?? []
        );

        return view('admin.stories.edit', compact('story', 'categories', 'subcategories') + ['effectKeys' => $this->effectKeys]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Story $story)
    {
        $data = $this->validatePayload($request, $story->id);

        Log::info('story.update.start', [
            'story_id' => $story->id,
            'slug' => $story->slug,
            'payload_slug' => $data['slug'] ?? null,
            'title' => $data['title'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->hasFile('audio_upload')) {
            $path = $request->file('audio_upload')->store('audio', 'public');
            $data['audio_path'] = $path;
            $data['audio_url'] = Storage::url($path);
            $this->enrichAudioMetadata($path, $data);
        }

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('images', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $data['effects'] = $this->extractEffects($request);
        $data['is_dummy'] = $request->boolean('is_dummy');
        $data['locked'] = $request->boolean('locked');
        $data['is_favorite'] = $request->boolean('is_favorite');

        $story->update($data);

        Log::info('story.update.done', ['story_id' => $story->id, 'slug' => $story->slug]);

        return redirect()->route('admin.stories.index')->with('status', 'Priča je ažurirana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Story $story)
    {
        $story->delete();

        return redirect()->route('admin.stories.index')->with('status', 'Priča je obrisana.');
    }

    private function validatePayload(Request $request, ?int $storyId = null): array
    {
        $storyId ??= 0;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:stories,slug,' . $storyId],
            'narrator' => ['nullable', 'string', 'max:190'],
            'duration_label' => ['nullable', 'string', 'max:50'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'is_dummy' => ['sometimes', 'boolean'],
            'locked' => ['sometimes', 'boolean'],
            'is_favorite' => ['sometimes', 'boolean'],
            'audio_url' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
            // Allow images up to 5 MB
            'image_upload' => ['nullable', 'image', 'max:20240'],
            'audio_upload' => ['nullable', 'file', 'max:20240'],
        ]);

        return $validated;
    }

    private function extractEffects(Request $request): array
    {
        $effects = [];
        foreach ($this->effectKeys as $key) {
            $effects[$key] = (int) $request->input("effects.{$key}", 0);
        }

        return $effects;
    }

    /**
     * Fill duration fields from the uploaded audio (if missing) using getID3/ffprobe when available.
     */
    private function enrichAudioMetadata(string $relativePath, array &$data): void
    {
        // If user already supplied duration, respect it.
        if (!empty($data['duration_seconds'])) {
            return;
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $duration = $this->probeDurationSeconds($fullPath);

        if ($duration) {
            $data['duration_seconds'] = (int) round($duration);
            // Only set a human label if not provided
            $data['duration_label'] = $data['duration_label'] ?? $this->formatDurationLabel($duration);
        } else {
            Log::warning('audio.duration.unavailable', ['path' => $fullPath]);
        }
    }

    private function probeDurationSeconds(string $fullPath): ?float
    {
        // Prefer getID3 if installed
        if (class_exists(getID3::class)) {
            try {
                $analyzer = new getID3();
                $info = $analyzer->analyze($fullPath);
                if (!empty($info['playtime_seconds'])) {
                    return (float) $info['playtime_seconds'];
                }
            } catch (\Throwable $e) {
                Log::warning('audio.duration.getid3_failed', ['path' => $fullPath, 'error' => $e->getMessage()]);
            }
        }

        // Fallback to ffprobe if present on the system
        $ffprobe = trim((string) shell_exec('where ffprobe 2>NUL'));
        if ($ffprobe) {
            $cmd = "\"{$ffprobe}\" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " .
                escapeshellarg($fullPath);
            $out = shell_exec($cmd);
            if ($out && is_numeric(trim($out))) {
                return (float) trim($out);
            }
        }

        return null;
    }

    private function formatDurationLabel(float $seconds): string
    {
        $minutes = max(1, (int) round($seconds / 60));
        return $minutes . ' min';
    }
}
