# ADR-004 — Stack and Core Packages

## Status
Proposed

## Decision
- PHP **8.4**
- Laravel **13**
- Filament **5**
- Pest for tests
- `spatie/laravel-permission` ^8
- `spatie/laravel-activitylog` ^5
- `spatie/laravel-backup` ^10

## Rationale
Greenfield project should track current supported majors. Activitylog v5 requires PHP 8.4, which also aligns with a clean Laravel 13 toolchain. Filament 5 is current and supports Laravel 13 (from 5.4+).

## Consequences
- Environment must provide PHP 8.4 before development/testing in CI or Cloud Agents.
- Avoid abandoned admin generators; prefer Filament native + policies.
