<?php

namespace Tests\Feature;

use App\Models\SocialPost;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialPublishTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The older migrations are MySQL-only; the in-memory sqlite DB only needs this table.
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_10_05_000001_create_social_posts_table.php']);

        config([
            'services.social.secret_hash' => Hash::make('tajna'),
            'services.meta.page_id' => 'PAGE',
            'services.meta.page_token' => 'TOKEN',
            'services.meta.ig_user_id' => 'IG',
            'services.meta.graph_version' => 'v23.0',
            'services.meta.media_url' => 'https://snovi.qla.dev',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (SocialPost::all() as $post) {
            File::delete(array_map('public_path', $post->slides ?? []));
        }

        parent::tearDown();
    }

    private function slide(): array
    {
        $image = imagecreatetruecolor(1200, 1200);
        imagefill($image, 0, 0, imagecolorallocate($image, 40, 30, 80));
        ob_start();
        imagejpeg($image);

        return ['image' => 'data:image/jpeg;base64,'.base64_encode(ob_get_clean()), 'text' => 'Večernji ritual za laku noć'];
    }

    public function test_wrong_secret_is_refused(): void
    {
        $this->postJson('/api/social/publish', ['secret' => 'kriva', 'type' => 'list'])->assertStatus(403);
    }

    public function test_slider_is_scheduled_and_published_to_facebook_and_instagram(): void
    {
        $response = $this->postJson('/api/social/publish', [
            'secret' => 'tajna',
            'type' => 'post',
            'topic' => 'ritual',
            'caption' => "Mirno veče 🌙\n\nhttps://snovi.fm/pretplata\n\n#snovifm",
            'scheduledAt' => now()->addHour()->toIso8601ZuluString(),
            'slides' => [$this->slide(), $this->slide(), $this->slide(), $this->slide()],
        ])->assertStatus(201);

        $this->assertCount(4, $response->json('data.slides'));
        $post = SocialPost::firstOrFail();
        $this->assertSame('scheduled', $post->status);
        $this->assertSame([1080, 1350], array_slice(getimagesize(public_path($post->slides[0])), 0, 2));

        Http::fake([
            'graph.facebook.com/v23.0/PAGE/photos' => Http::sequence()->push(['id' => 'p1'])->push(['id' => 'p2'])->push(['id' => 'p3'])->push(['id' => 'p4']),
            'graph.facebook.com/v23.0/PAGE/feed' => Http::response(['id' => 'PAGE_123']),
            'graph.facebook.com/v23.0/IG/media_publish' => Http::response(['id' => 'IGMEDIA']),
            'graph.facebook.com/v23.0/IG/media' => Http::sequence()->push(['id' => 'c1'])->push(['id' => 'c2'])->push(['id' => 'c3'])->push(['id' => 'c4'])->push(['id' => 'carousel']),
            'graph.facebook.com/v23.0/*' => Http::response(['status_code' => 'FINISHED']),
        ]);

        // Not due yet.
        $this->artisan('social:publish-due')->assertSuccessful();
        Http::assertNothingSent();

        $this->travel(61)->minutes();
        $this->artisan('social:publish-due')->assertSuccessful();

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertSame('PAGE_123', $post->fb_post_id);
        $this->assertSame('IGMEDIA', $post->ig_media_id);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/PAGE/feed')
            && $request['attached_media[3]'] === '{"media_fbid":"p4"}');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/IG/media')
            && ($request['media_type'] ?? null) === 'CAROUSEL'
            && $request['children'] === 'c1,c2,c3,c4'
            && str_contains($request['caption'], 'link u opisu profila'));
    }
}
