<?php

use App\Http\Controllers\BackupDownloadController;
use App\Http\Controllers\PrivateMediaDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])
    ->get('/admin/media/{media}/download', PrivateMediaDownloadController::class)
    ->name('media.private.download');

Route::middleware(['web', 'auth'])
    ->get('/admin/backups/{backup}/download', BackupDownloadController::class)
    ->name('backups.download');
