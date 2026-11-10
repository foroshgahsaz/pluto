<?php

use App\Http\Controllers\BackupDownloadController;
use App\Http\Controllers\PrivateMediaDownloadController;
use App\Http\Controllers\PublicSite\ArticleController;
use App\Http\Controllers\PublicSite\CategoryController;
use App\Http\Controllers\PublicSite\FeedController;
use App\Http\Controllers\PublicSite\FormController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\PageController;
use App\Http\Controllers\PublicSite\SitemapController;
use App\Http\Middleware\ResolvePublicSite;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'central'])->name('public.home.central');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('public.robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap.index');

Route::middleware(['web', 'auth'])
    ->get('/admin/media/{media}/download', PrivateMediaDownloadController::class)
    ->name('media.private.download');

Route::middleware(['web', 'auth'])
    ->get('/admin/backups/{backup}/download', BackupDownloadController::class)
    ->name('backups.download');

Route::prefix('{site:slug}')
    ->where(['site' => '^(?!admin$|livewire$|storage$|up$|filament$)[^/]+$'])
    ->middleware([ResolvePublicSite::class])
    ->group(function (): void {
        Route::get('/', [HomeController::class, 'show'])->name('public.home');
        Route::get('/news', [ArticleController::class, 'index'])->name('public.news');
        Route::get('/news/{slug}', [ArticleController::class, 'show'])->name('public.article');
        Route::get('/search', [ArticleController::class, 'search'])->name('public.search');
        Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('public.category');
        Route::get('/page/{slug}', [PageController::class, 'show'])->name('public.page');
        Route::get('/forms/{slug}', [FormController::class, 'show'])->name('public.form');
        Route::post('/forms/{slug}', [FormController::class, 'submit'])
            ->middleware('throttle:10,1')
            ->name('public.form.submit');
        Route::get('/rss.xml', [FeedController::class, 'rss'])->name('public.rss');
        Route::get('/sitemap.xml', [SitemapController::class, 'site'])->name('public.sitemap');
    });
