# ADR-003 — Filament Admin First; Public Frontend Deferred

## Status
Superseded for Stage 9+ (owner continued after Stage 8)

## Context
Master scope deferred public templates until backend approval. Stages 2–8 stayed admin-only.

## Decision
Stages 2–8: Filament administration + public **data structures/services** only.  
Stage 9: owner continuation unlocked subdirectory public Blade frontend.

## Consequences
- Backend delivered without blocking on public UX.
- Stage 9 consumes settings/menus/homepage sections/PublicArticleQuery.
