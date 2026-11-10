# 12 — Form Builder (Design)

**Status:** Designed — Not Started

## Purpose
Per-site dynamic forms with structured field definitions, conditional visibility, multi-step support, secure submission storage, and authorized export.

## Storage model
- `forms` — definition metadata
- `form_fields` — ordered fields with JSON config for options, validation, visibility, step
- `form_submissions` — immutable-ish payload snapshots
- `form_submission_files` — uploaded files linked to media

Field definitions are **data**, not generated PHP classes. Runtime builds Laravel validation arrays from `validation_rules_json` + field type defaults.

## Field types (MVP)
`text`, `textarea`, `number`, `date`, `select`, `multiselect`, `file`, `image`

## Conditional visibility
- Evaluated in admin preview with Alpine (UX only).
- On submit: server recomputes visibility; hidden non-applicable fields ignored; required rules apply only when visible.

## Admin UX
- Filament resource for forms
- Alpine + SortableJS for field reorder / step management
- Submission Relation Manager with export action (CSV) under permission

## Security
- Site scope on form + submissions + files
- Export authorization required
- File fields use same media security pipeline
- Rate-limit public submit endpoints when public frontend arrives
