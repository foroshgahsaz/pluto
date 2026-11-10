# Module: Forms

**Status:** Implemented · Tested

## Module purpose
Dynamic multi-site form builder and submission management.

## Related source files
See `DOCUMENTATION/12-FORM-BUILDER.md`.

## Testing procedures
```bash
php artisan test --filter=FormBuilderTest
```

## Extending
Add new `FormFieldType` cases + default rules; optional Alpine preview can call the same visibility evaluator via a Livewire endpoint later.
