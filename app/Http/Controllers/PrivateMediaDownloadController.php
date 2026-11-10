<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Support\Tenancy\SiteScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaDownloadController extends Controller
{
    public function __invoke(Request $request, int $media): StreamedResponse
    {
        $record = Media::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->findOrFail($media);

        abort_unless($request->user(), 403);
        Gate::authorize('downloadPrivate', $record);

        if (! $record->isPrivate()) {
            abort(404);
        }

        abort_unless(Storage::disk($record->disk)->exists($record->path), 404);

        activity('media')
            ->causedBy($request->user())
            ->performedOn($record)
            ->withProperties(['site_id' => $record->site_id])
            ->log('media.private_downloaded');

        return Storage::disk($record->disk)->download(
            $record->path,
            $record->original_name,
            [
                'Content-Type' => $record->mime_type ?: 'application/octet-stream',
            ]
        );
    }
}
