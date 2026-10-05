<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\MetaSocialPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/** Posts due sliders to Facebook and Instagram; the scheduler runs it every minute. */
class PublishDueSocialPosts extends Command
{
    protected $signature = 'social:publish-due {--limit=2}';

    protected $description = 'Publish scheduled snovi.fm sliders whose time has come to Facebook and Instagram';

    public function handle(MetaSocialPublisher $publisher): int
    {
        $due = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->oldest('scheduled_at')
            ->limit((int) $this->option('limit'))
            ->get();

        $run = ['at' => now()->toIso8601ZuluString(), 'posts' => []];
        foreach ($due as $post) {
            $result = $publisher->publish($post);
            $line = "#{$post->id} {$result['status']} (facebook {$result['facebook']['status']}, instagram {$result['instagram']['status']})";
            $this->line($line);
            $run['posts'][] = $line;
        }

        // Heartbeat, to see from the server whether the cron runs at all.
        File::put(storage_path('app/social-cron.json'), json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
