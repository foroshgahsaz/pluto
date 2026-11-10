# ADR-001 — Single Database Logical Tenancy

## Status
Proposed (Stage 1)

## Context
Pluto must host many regional news sites from one admin panel without installing a new Laravel app per region. Sites are organizational regions, not isolated SaaS customers with separate databases.

## Decision
Use **one MySQL database** and isolate data with **`site_id` + global scopes + policies**. Do not use database-per-tenant packages (e.g. Stancl Tenancy) for MVP.

## Consequences
- Simpler ops, transactions, reporting, Filament integration.
- Cross-site leakage is the primary risk — mitigated by mandatory isolation tests.
- Future physical sharding remains possible but is out of scope.
