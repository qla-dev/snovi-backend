<?php

namespace App\Services;

use App\Models\SocialPost;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Publishes a SocialPost as a slider on the snovi.fm Facebook page (multi-photo post) and as a
 * carousel on Instagram, through the Graph API. Ported from geovizija's MetaPublisher and
 * InstagramPublisher.
 *
 * Both run from `social:publish-due` once scheduled_at has passed: Instagram cannot schedule
 * through the API, and publishing both at the same minute keeps them in step.
 *
 * Never throws: each network returns ['status' => posted|skipped|failed, 'message' => ...].
 */
class MetaSocialPublisher
{
    public static function facebookConfigured(): bool
    {
        return filled(config('services.meta.page_id')) && filled(config('services.meta.page_token'));
    }

    public static function instagramConfigured(): bool
    {
        return filled(config('services.meta.ig_user_id')) && filled(config('services.meta.page_token'));
    }

    /** Publishes on whatever is still missing and stores the outcome on the post. */
    public function publish(SocialPost $post): array
    {
        $post->forceFill(['status' => 'publishing'])->save();

        $facebook = $this->facebook($post);
        $instagram = $this->instagram($post);

        $results = array_filter([$facebook['status'], $instagram['status']], fn ($status) => $status !== 'skipped');
        $status = match (true) {
            $results === [] || ! in_array('failed', $results, true) => 'published',
            in_array('posted', $results, true) => 'partial',
            default => 'failed',
        };

        $post->forceFill([
            'status' => $status,
            'published_at' => $status === 'failed' ? null : now(),
        ])->save();

        Log::channel('social')->info("#{$post->id} {$status}: facebook {$facebook['status']} ({$facebook['message']}), instagram {$instagram['status']} ({$instagram['message']})");

        return ['status' => $status, 'facebook' => $facebook, 'instagram' => $instagram];
    }

    /**
     * Facebook slider: every photo is uploaded unpublished, then one feed post attaches them all.
     */
    private function facebook(SocialPost $post): array
    {
        if (! $post->share_facebook) {
            return ['status' => 'skipped', 'message' => 'shareToFacebook: false'];
        }
        if ($post->fb_post_id) {
            return ['status' => 'skipped', 'message' => 'Već objavljeno na Facebooku.'];
        }
        if (! self::facebookConfigured()) {
            return ['status' => 'skipped', 'message' => 'Facebook nije povezan (META_PAGE_ID / META_PAGE_TOKEN).'];
        }

        $pageId = config('services.meta.page_id');
        $token = config('services.meta.page_token');

        try {
            $attached = [];
            foreach ($post->slides as $index => $path) {
                $photo = Http::asForm()->timeout(60)->post($this->graph("{$pageId}/photos"), [
                    'url' => SocialPost::publicUrl($path),
                    'published' => 'false',
                    'access_token' => $token,
                ]);
                if (! $photo->successful() || ! $photo->json('id')) {
                    return $this->failFacebook($post, 'Facebook je odbio sliku '.($index + 1).': '.$this->error($photo));
                }
                $attached['attached_media['.$index.']'] = json_encode(['media_fbid' => (string) $photo->json('id')]);
            }

            $feed = Http::asForm()->timeout(60)->post($this->graph("{$pageId}/feed"), $attached + [
                'message' => $post->caption,
                'access_token' => $token,
            ]);
        } catch (Throwable $exception) {
            return $this->failFacebook($post, 'Facebook nije dostupan: '.$exception->getMessage());
        }

        if (! $feed->successful() || ! $feed->json('id')) {
            return $this->failFacebook($post, 'Facebook je odbio objavu: '.$this->error($feed));
        }

        $post->forceFill(['fb_post_id' => (string) $feed->json('id'), 'fb_error' => null])->save();

        return ['status' => 'posted', 'message' => 'Objavljeno na Facebooku.', 'postId' => $post->fb_post_id];
    }

    /**
     * Instagram carousel: one container per slide (is_carousel_item), a CAROUSEL container with
     * the children and the caption, then media_publish.
     */
    private function instagram(SocialPost $post): array
    {
        if (! $post->share_instagram) {
            return ['status' => 'skipped', 'message' => 'shareToInstagram: false'];
        }
        if ($post->ig_media_id) {
            return ['status' => 'skipped', 'message' => 'Već objavljeno na Instagramu.'];
        }
        if (! self::instagramConfigured()) {
            return ['status' => 'skipped', 'message' => 'Instagram nije povezan (META_IG_USER_ID / META_PAGE_TOKEN).'];
        }

        try {
            $children = [];
            foreach ($post->slides as $index => $path) {
                $child = $this->container([
                    'image_url' => SocialPost::publicUrl($path),
                    'is_carousel_item' => 'true',
                ]);
                if (isset($child['error'])) {
                    return $this->failInstagram($post, 'Slika '.($index + 1).': '.$child['error']);
                }
                $children[] = $child['id'];
            }

            $carousel = $this->container([
                'media_type' => 'CAROUSEL',
                'children' => implode(',', $children),
                'caption' => $this->instagramCaption($post),
            ]);
            if (isset($carousel['error'])) {
                return $this->failInstagram($post, $carousel['error']);
            }

            $published = Http::asForm()->timeout(60)->post($this->graph(config('services.meta.ig_user_id').'/media_publish'), [
                'creation_id' => $carousel['id'],
                'access_token' => config('services.meta.page_token'),
            ]);
        } catch (Throwable $exception) {
            return $this->failInstagram($post, 'Instagram nije dostupan: '.$exception->getMessage());
        }

        if (! $published->successful() || ! $published->json('id')) {
            return $this->failInstagram($post, 'Instagram je odbio objavu: '.$this->error($published));
        }

        $post->forceFill(['ig_media_id' => (string) $published->json('id'), 'ig_error' => null])->save();

        return ['status' => 'posted', 'message' => 'Objavljeno na Instagramu.', 'mediaId' => $post->ig_media_id];
    }

    /**
     * Creates a media container and waits until Instagram has downloaded and processed it.
     *
     * @return array{id: string}|array{error: string}
     */
    private function container(array $params): array
    {
        $token = config('services.meta.page_token');
        $container = Http::asForm()->timeout(60)->post($this->graph(config('services.meta.ig_user_id').'/media'), $params + ['access_token' => $token]);
        if (! $container->successful() || ! $container->json('id')) {
            return ['error' => 'Instagram je odbio sliku: '.$this->error($container)];
        }

        $id = (string) $container->json('id');
        for ($try = 0; $try < 15; $try++) {
            $status = Http::timeout(20)->get($this->graph($id), ['fields' => 'status_code', 'access_token' => $token])->json('status_code');
            if ($status === 'FINISHED') {
                return ['id' => $id];
            }
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                return ['error' => "Instagram nije obradio sliku ({$status})."];
            }
            Sleep::for(2)->seconds();
        }

        return ['id' => $id];
    }

    /** Captions have no clickable links on Instagram, so the link points to the profile bio. */
    private function instagramCaption(SocialPost $post): string
    {
        $caption = str_replace(['https://snovi.fm/pretplata', 'https://snovi.fm'], 'link u opisu profila', $post->caption);

        return mb_substr($caption, 0, 2200);
    }

    private function error(Response $response): string
    {
        return (string) ($response->json('error.message') ?? "HTTP {$response->status()}");
    }

    private function graph(string $path): string
    {
        return sprintf('https://graph.facebook.com/%s/%s', config('services.meta.graph_version'), $path);
    }

    private function failFacebook(SocialPost $post, string $message): array
    {
        $post->forceFill(['fb_error' => mb_substr($message, 0, 500)])->save();
        Log::channel('social')->warning("#{$post->id} facebook failed: {$message}");

        return ['status' => 'failed', 'message' => $message];
    }

    private function failInstagram(SocialPost $post, string $message): array
    {
        $post->forceFill(['ig_error' => mb_substr($message, 0, 500)])->save();
        Log::channel('social')->warning("#{$post->id} instagram failed: {$message}");

        return ['status' => 'failed', 'message' => $message];
    }
}
