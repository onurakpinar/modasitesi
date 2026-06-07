<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Yayın zamanı gelen zamanlanmış yazıları yayına alır';

    public function handle(): int
    {
        $posts = Post::query()
            ->where('status', PostStatus::Scheduled)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at')
            ->get();

        if ($posts->isEmpty()) {
            $this->comment('Yayınlanacak zamanlanmış yazı yok.');

            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            $post->update(['status' => PostStatus::Published]);
            $this->line("Yayınlandı: {$post->title} (#{$post->id})");
        }

        $this->info("{$posts->count()} yazı yayına alındı.");

        return self::SUCCESS;
    }
}
