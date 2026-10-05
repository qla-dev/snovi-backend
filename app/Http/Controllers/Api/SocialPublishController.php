<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Services\MetaSocialPublisher;
use App\Services\SocialSlideRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Endpoint for the Claude agent that schedules the daily snovi.fm sliders (see
 * rutina-social-prompt.txt). Public route; the body carries the publish secret, checked against
 * its bcrypt hash (SOCIAL_PUBLISH_SECRET_HASH), as on geovizija's /publish.
 *
 * type "post"            new slider: caption + 2-10 slides + scheduledAt (UTC with Z)
 * type "post" with "id"  change a slider that is not out yet (or "cancel": true)
 * type "list"            recent and upcoming sliders, so the agent avoids repeats and gaps
 *
 * The slides are drawn here (SocialSlideRenderer) and social:publish-due posts them to Facebook
 * and Instagram once scheduledAt has passed.
 */
class SocialPublishController extends Controller
{
    public function __invoke(Request $request, SocialSlideRenderer $renderer): JsonResponse
    {
        $secret = (string) $request->input('secret', '');
        $hash = (string) config('services.social.secret_hash');
        if ($secret === '' || $hash === '' || ! Hash::check($secret, $hash)) {
            return response()->json(['message' => 'Neispravan ili nedostaje "secret" — ništa nije zakazano.'], 403);
        }

        try {
            return match ($request->input('type')) {
                'post' => $request->filled('id') ? $this->update($request, $renderer) : $this->create($request, $renderer),
                'list' => $this->list($request),
                default => response()->json(['message' => '"type" mora biti "post" ili "list".'], 422),
            };
        } catch (ValidationException $exception) {
            return response()->json(['message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
        }
    }

    private function create(Request $request, SocialSlideRenderer $renderer): JsonResponse
    {
        $data = $request->validate([
            'caption' => ['required', 'string', 'max:2200'],
            'topic' => ['nullable', 'string', 'max:255'],
            'scheduledAt' => ['required', 'date'],
            'slides' => ['required', 'array', 'min:2', 'max:10'],
            'slides.*.image' => ['required', 'string'],
            'slides.*.text' => ['nullable', 'string', 'max:140'],
            'sources' => ['nullable', 'array'],
            'shareToFacebook' => ['nullable', 'boolean'],
            'shareToInstagram' => ['nullable', 'boolean'],
        ]);

        $scheduledAt = $this->scheduledAt($data['scheduledAt']);

        ignore_user_abort(true);
        set_time_limit(300);

        $paths = $this->renderSlides($renderer, $data['slides']);

        $post = SocialPost::create([
            'topic' => $data['topic'] ?? null,
            'caption' => trim($data['caption']),
            'slides' => $paths,
            'sources' => $data['sources'] ?? null,
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
            'share_facebook' => $data['shareToFacebook'] ?? true,
            'share_instagram' => $data['shareToInstagram'] ?? true,
        ]);

        return response()->json([
            'message' => 'Slajder je zakazan za '.$scheduledAt->copy()->setTimezone('Europe/Sarajevo')->format('d.m.Y. H:i').' (Sarajevo).',
            'warnings' => $this->warnings($post),
            'data' => $post->toApi(),
        ], 201);
    }

    private function update(Request $request, SocialSlideRenderer $renderer): JsonResponse
    {
        $post = SocialPost::find($request->input('id'));
        if (! $post) {
            return response()->json(['message' => 'Slajder s tim id ne postoji.'], 404);
        }
        if (! in_array($post->status, ['scheduled', 'failed'], true)) {
            return response()->json(['message' => "Slajder je već {$post->status}; mijenja se samo zakazan ili neuspio.", 'data' => $post->toApi()], 409);
        }

        $data = $request->validate([
            'cancel' => ['nullable', 'boolean'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'topic' => ['nullable', 'string', 'max:255'],
            'scheduledAt' => ['nullable', 'date'],
            'slides' => ['nullable', 'array', 'min:2', 'max:10'],
            'slides.*.image' => ['required_with:slides', 'string'],
            'slides.*.text' => ['nullable', 'string', 'max:140'],
            'sources' => ['nullable', 'array'],
            'shareToFacebook' => ['nullable', 'boolean'],
            'shareToInstagram' => ['nullable', 'boolean'],
        ]);

        if ($data['cancel'] ?? false) {
            $post->forceFill(['status' => 'cancelled'])->save();

            return response()->json(['message' => 'Slajder je otkazan.', 'data' => $post->toApi()]);
        }

        $changed = [];
        foreach (['caption' => 'caption', 'topic' => 'topic', 'sources' => 'sources', 'shareToFacebook' => 'share_facebook', 'shareToInstagram' => 'share_instagram'] as $field => $column) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $post->{$column} = $data[$field];
                $changed[] = $field;
            }
        }
        if (! empty($data['scheduledAt'])) {
            $post->scheduled_at = $this->scheduledAt($data['scheduledAt']);
            $changed[] = 'scheduledAt';
        }
        if (! empty($data['slides'])) {
            set_time_limit(300);
            $old = $post->slides ?? [];
            $post->slides = $this->renderSlides($renderer, $data['slides']);
            File::delete(array_map('public_path', $old));
            $changed[] = 'slides';
        }

        // A failed slider that gets a new time (or new slides) is retried on whatever did not go out.
        $post->status = 'scheduled';
        $post->save();

        return response()->json([
            'message' => $changed ? 'Slajder je ažuriran.' : 'Ništa nije promijenjeno.',
            'changed' => $changed,
            'warnings' => $this->warnings($post),
            'data' => $post->toApi(),
        ]);
    }

    private function list(Request $request): JsonResponse
    {
        $days = max(1, min(60, (int) $request->input('days', 14)));
        $posts = SocialPost::where('scheduled_at', '>=', now()->subDays($days))
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'now' => now()->toIso8601ZuluString(),
            'timezone' => 'Europe/Sarajevo',
            'facebookConnected' => MetaSocialPublisher::facebookConfigured(),
            'instagramConnected' => MetaSocialPublisher::instagramConfigured(),
            'posts' => $posts->map->toApi()->values(),
        ]);
    }

    /** @param list<array{image: string, text?: string|null}> $slides */
    private function renderSlides(SocialSlideRenderer $renderer, array $slides): array
    {
        $batch = now()->format('YmdHis').'-'.Str::lower(Str::random(6));
        $paths = [];

        try {
            foreach ($slides as $index => $slide) {
                $paths[] = $renderer->render($slide['image'], $slide['text'] ?? null, "slider-{$batch}-".($index + 1));
            }
        } catch (Throwable $exception) {
            File::delete(array_map('public_path', $paths));

            throw ValidationException::withMessages([
                'slides.'.count($paths) => 'Slajd '.(count($paths) + 1).': '.$exception->getMessage(),
            ]);
        }

        return $paths;
    }

    private function scheduledAt(string $value): Carbon
    {
        $at = Carbon::parse($value)->utc();
        if ($at->lt(now()->subMinutes(5))) {
            throw ValidationException::withMessages(['scheduledAt' => 'scheduledAt je u prošlosti (šalji UTC sa "Z").']);
        }
        if ($at->gt(now()->addDays(30))) {
            throw ValidationException::withMessages(['scheduledAt' => 'scheduledAt može biti najviše 30 dana unaprijed.']);
        }

        return $at;
    }

    /** Sliders closer than 90 minutes to another one on the same day crowd the feed. */
    private function warnings(SocialPost $post): array
    {
        $near = SocialPost::whereKeyNot($post->id)
            ->whereIn('status', ['scheduled', 'published', 'partial'])
            ->whereBetween('scheduled_at', [$post->scheduled_at->copy()->subMinutes(90), $post->scheduled_at->copy()->addMinutes(90)])
            ->pluck('id');

        $warnings = $near->isEmpty() ? [] : ['Manje od 90 min od slajdera: '.$near->implode(', ').'.'];
        if (! MetaSocialPublisher::facebookConfigured() && $post->share_facebook) {
            $warnings[] = 'Facebook nije povezan na serveru (META_PAGE_ID / META_PAGE_TOKEN).';
        }
        if (! MetaSocialPublisher::instagramConfigured() && $post->share_instagram) {
            $warnings[] = 'Instagram nije povezan na serveru (META_IG_USER_ID).';
        }

        return $warnings;
    }
}
