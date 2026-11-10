# 11 — Media Management

**Status:** Implemented · Tested (STAGE 4)

## Purpose
Site-scoped media library for images/documents with folders, search/filters, private downloads, and secure uploads.

## Capabilities
- Upload images and documents with MIME + extension validation
- Folder tree per site
- Search/filter by name, category, visibility, folder, site
- Image preview in Filament for public images
- Private files stored on `local` disk; authorized download route
- Soft delete + weekly orphan purge command
- `MalwareScanner` integration point (`NullMalwareScanner` by default)

## Source files
- Models: `Media`, `MediaFolder`
- `App\Services\Media\MediaUploadService`
- `App\Http\Controllers\PrivateMediaDownloadController`
- Filament: `MediaResource`, `MediaFolderResource`
- Command: `media:purge-orphans`
- Route: `GET /admin/media/{media}/download` (`media.private.download`)

## Permissions
`media.view`, `media.upload`, `media.update`, `media.delete`, `media.download_private`

## Security
- Extension denylist + finfo MIME allowlist
- Size limits (`config/pluto.php`)
- UUID storage names
- Private disk outside public web root
- Cross-site download denied (tested)

## Tests
`tests/Feature/MediaSecurityTest.php`
