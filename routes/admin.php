<?php

use App\Http\Controllers\Admin\AdSenseController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentBriefController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostPreviewController;
use App\Http\Controllers\Admin\PostRevisionController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware('admin')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('posts/{post}/preview', [PostPreviewController::class, 'show'])
            ->middleware('signed')
            ->name('posts.preview');
        Route::post('posts/{post}/revisions/{revision}/restore', [PostRevisionController::class, 'restore'])
            ->name('posts.revisions.restore');
        Route::resource('posts', PostController::class)->except(['show']);
        Route::patch('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);
        Route::resource('authors', AuthorController::class)->except(['show']);
        Route::resource('pages', PageController::class)->except(['show']);
        Route::resource('content-briefs', ContentBriefController::class)->except(['show', 'destroy']);

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markAsRead'])->name('contact-messages.read');

        Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

        Route::get('adsense', [AdSenseController::class, 'edit'])->name('adsense.edit');
        Route::put('adsense', [AdSenseController::class, 'update'])->name('adsense.update');

        Route::middleware('super_admin')->group(function () {
            Route::delete('content-briefs/{content_brief}', [ContentBriefController::class, 'destroy'])
                ->name('content-briefs.destroy');
            Route::resource('users', UserController::class)->except(['show']);
        });
    });
});
