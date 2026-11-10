<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupDownloadController extends Controller
{
    public function __invoke(Request $request, BackupRecord $backup): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isSuperAdmin(), 403);
        abort_unless($backup->isSuccessful() && $backup->fileExists(), 404);

        activity('backup')
            ->causedBy($user)
            ->performedOn($backup)
            ->withProperties([
                'disk' => $backup->disk,
                'path' => $backup->path,
            ])
            ->log('backup.downloaded');

        return Storage::disk($backup->disk)->download(
            $backup->path,
            basename((string) $backup->path),
            ['Content-Type' => 'application/zip'],
        );
    }
}
