# Module: Editorial

**Status:** Implemented · Tested

## Module purpose
Full editorial CMS backend for multi-site news.

## Module responsibilities
- Article CRUD and taxonomy (categories/subcategories, tags, keywords)
- Authors and content manager assignment
- Workflow transitions with authorization
- Revisions on create/update/status change
- Featured image + attachment relation model
- Scheduled publishing job
- Public data query helpers (no frontend)

## Related source files
See `DOCUMENTATION/10-NEWS-EDITORIAL-SYSTEM.md`.

## Required permissions
`articles.*` (+ category/tag/author access via article permissions)

## Testing procedures
```bash
php artisan test --filter=Editorial
```

## Security considerations
Tenant isolation on all editorial tables; publish/approve gated by permissions; activity log on status changes.
