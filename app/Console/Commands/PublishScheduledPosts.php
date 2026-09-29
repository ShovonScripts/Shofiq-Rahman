<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\CacheKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish scheduled posts whose publication time has arrived';

    public function handle(): int
    {
        $count = Post::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            foreach ([CacheKeys::latestPosts(), CacheKeys::categoriesWithPostCount(), 'rss.items', 'sitemap.xml'] as $key) {
                Cache::forget($key);
            }
        }

        $this->info("Published {$count} scheduled post(s).");

        return self::SUCCESS;
    }
}
