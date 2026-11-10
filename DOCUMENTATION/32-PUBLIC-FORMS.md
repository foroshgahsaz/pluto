# 32 — Public Forms

**Status:** Implemented — Stage 10

## Routes
| Method | URL | Notes |
|--------|-----|-------|
| GET | `/{site}/forms/{slug}` | Active forms only |
| POST | `/{site}/forms/{slug}` | Throttled `10/min`; uses `SubmitFormService` |

## Behavior
- Server-side validation + conditional visibility (same as admin builder)
- Alpine.js mirrors visibility for UX; server remains authoritative
- Multi-step forms (`is_multi_step` + field `step`)
- File/image uploads stored as **private** media via `MediaUploadService`
- Success message from form settings; inactive/cross-site slugs → 404

## Tests
`tests/Feature/PublicFormsTest.php`
