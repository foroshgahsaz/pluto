# 11 — Media Management (Design)

**Status:** Designed — Not Started

## Purpose
Site-scoped media library for images, documents, and attachments with private/public visibility.

## Capabilities
- Upload to folder tree
- Categorize via folders
- Search/filter by name, mime, date, uploader
- Preview supported images/PDFs in Filament
- Attach to articles/pages/form submissions
- Soft-delete + cleanup job for orphaned files
- Authorized private download endpoint

## Security rules
1. Validate real MIME (finfo) + extension allowlist.
2. Max size from config (e.g. images 5MB, docs 20MB — finalize in STAGE 4).
3. Random storage filename; keep original_name for display only.
4. `private` disk outside public web root.
5. Download route: auth + `media.download_private` + same `site_id`.
6. Interface `MalwareScanner` with null implementation until AV approved.

## Filament
MediaResource + Folder management (Alpine/Sortable only if drag-reorder folders is required; otherwise table actions suffice).
