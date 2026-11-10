# 12 — Form Builder

**Status:** Implemented · Tested (STAGE 5)

## Purpose
Per-site dynamic forms with structured field definitions, conditional visibility, multi-step support, secure submission storage, and authorized export.

## Storage model
- `forms` — definition metadata
- `form_fields` — ordered fields with JSON config for options, validation, visibility, step
- `form_submissions` — payload snapshots
- `form_submission_files` — uploaded files linked to media

## Field types (MVP)
`text`, `textarea`, `number`, `date`, `select`, `multiselect`, `file`, `image`

## Conditional visibility
- Admin configures JSON rules on fields
- On submit, `FormVisibilityEvaluator` recomputes visibility server-side
- Hidden fields are not validated as required and are stripped from stored payload

## Admin UX
- Filament `FormResource`
- Fields relation manager with drag reorder (`sort_order`)
- Submissions relation manager with view/delete + CSV export

## Services
- `SubmitFormService`
- `BuildFormValidationRules`
- `FormVisibilityEvaluator`
- `ExportFormSubmissionsService`

## Security
- Site scope on forms/fields/submissions/files
- Export requires `form_submissions.export`
- Upload fields use private media pipeline
- Cross-site submission access denied (tested)

## Tests
`tests/Feature/FormBuilderTest.php`
