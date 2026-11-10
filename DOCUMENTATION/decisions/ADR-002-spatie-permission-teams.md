# ADR-002 — Spatie Permission Teams Mapped to Sites

## Status
Proposed (awaiting owner choice vs Option B)

## Context
Users may work on multiple regional sites with different responsibilities.

## Decision (recommended)
Enable Spatie **teams**, mapping `team_id` to `sites.id`, plus `site_user` membership table.

## Alternative
Global roles only + membership (simpler; less flexible).

## Consequences
- Option A: more accurate org modeling; slightly more complex seeding/UI.
- Option B: faster MVP; may force workarounds if roles diverge per site.
