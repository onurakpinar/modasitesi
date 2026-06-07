<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Observers\PostObserver;
use App\View\Composers\SiteComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);

        Paginator::defaultView('vendor.pagination.tailwind');

        RateLimiter::for('admin-login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by(Str::lower($email).'|'.$request->ip());
        });

        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        View::composer(
            [
                'layouts.app',
                'components.site-header',
                'components.site-footer',
                'home',
                'posts.*',
                'categories.*',
                'tags.*',
                'authors.*',
                'search.*',
                'pages.*',
            ],
            SiteComposer::class
        );

        View::composer(['layouts.admin', 'components.admin-sidebar'], function ($view) {
            $view->with('siteName', config('site.name'));
            $view->with('siteTagline', config('site.tagline'));
            $view->with('unreadContactCount', $this->unreadContactCount());
        });
    }

    private function unreadContactCount(): int
    {
        if (! Schema::hasTable('contact_messages')) {
            return 0;
        }

        return ContactMessage::query()->whereNull('read_at')->count();
    }
}
