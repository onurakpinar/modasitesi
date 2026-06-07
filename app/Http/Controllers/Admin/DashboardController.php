<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BriefStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\ContentBrief;
use App\Models\Post;
use App\Support\PostQualityChecker;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PostQualityChecker $qualityChecker): View
    {
        $draftPosts = Post::query()
            ->with(['author', 'category'])
            ->where('status', PostStatus::Draft)
            ->latest()
            ->get();

        $readyDrafts = $draftPosts->filter(fn (Post $post) => $qualityChecker->isPublishable($post));
        $incompleteDrafts = $draftPosts->reject(fn (Post $post) => $qualityChecker->isPublishable($post));

        $missingCoverPosts = Post::query()
            ->whereIn('status', [PostStatus::Draft, PostStatus::Scheduled])
            ->where(function ($query) {
                $query->whereNull('cover_image')->orWhere('cover_image', '');
            })
            ->latest()
            ->limit(10)
            ->get();

        $lowWordPosts = Post::query()
            ->whereIn('status', [PostStatus::Draft, PostStatus::Scheduled])
            ->get()
            ->filter(fn (Post $post) => PostQualityChecker::wordCount($post->body) < PostQualityChecker::MIN_WORD_COUNT)
            ->take(10)
            ->values();

        $pendingReviewPosts = Post::query()
            ->whereIn('status', [PostStatus::Draft, PostStatus::Scheduled])
            ->whereNull('human_reviewed_at')
            ->latest()
            ->limit(10)
            ->get();

        $publishedPosts = Post::query()
            ->where('status', PostStatus::Published)
            ->get();

        return view('admin.dashboard', [
            'publishedCount' => $publishedPosts->count(),
            'applicationReadyPublishedCount' => $publishedPosts
                ->filter(fn (Post $post) => $qualityChecker->isPublishable($post))
                ->count(),
            'briefTotalCount' => ContentBrief::query()->count(),
            'briefPreparingCount' => ContentBrief::query()->where('status', BriefStatus::Preparing)->count(),
            'briefReviewCount' => ContentBrief::query()->where('status', BriefStatus::Review)->count(),
            'briefCompletedCount' => ContentBrief::query()->where('status', BriefStatus::Completed)->count(),
            'draftCount' => $draftPosts->count(),
            'categoryCount' => Category::query()->count(),
            'unreadMessages' => ContactMessage::query()->whereNull('read_at')->count(),
            'recentPosts' => Post::query()
                ->with(['author', 'category'])
                ->latest()
                ->limit(5)
                ->get(),
            'readyDrafts' => $readyDrafts,
            'incompleteDrafts' => $incompleteDrafts,
            'missingCoverPosts' => $missingCoverPosts,
            'lowWordPosts' => $lowWordPosts,
            'pendingReviewPosts' => $pendingReviewPosts,
        ]);
    }
}
