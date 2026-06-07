<?php

use App\Http\Controllers\AdsTxtController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ContactController;
use App\Support\PublicContent;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');
Route::get('/rss.xml', [RssController::class, 'index'])->name('rss');
Route::get('/ads.txt', [AdsTxtController::class, 'index'])->name('ads.txt');

Route::get('/yazilar', [PostController::class, 'index'])->name('posts.index');
Route::get('/yazi/{slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/etiket/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/yazar/{slug}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/arama', [SearchController::class, 'index'])
    ->middleware('throttle:search')
    ->name('search');

Route::post('/iletisim', [ContactController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');

foreach (PublicContent::staticPageRoutes() as $routeName => $slug) {
    Route::get('/'.$slug, [PageController::class, 'show'])
        ->defaults('slug', $slug)
        ->name($routeName);
}
