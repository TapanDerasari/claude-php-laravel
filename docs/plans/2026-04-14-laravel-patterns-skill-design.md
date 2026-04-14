# Laravel Patterns Skill — Design

**Date:** 2026-04-14
**Branch:** feat/v1-kit

## Goal

Add a single `laravel-patterns` skill that fills the architectural gaps in the kit: Controllers→Services→Actions, DTOs, Service Container bindings, consistent API response shape, Query Objects, transactions, caching, and events/queues. It cross-references existing artifacts (eloquent-query-builder, artisan-make, pest-test-writer, make-migration) rather than duplicating their domains.

## Artifact

- **File:** `laravel/skills/laravel-patterns/SKILL.md`
- **paste-into:** `.claude/skills/laravel-patterns/`
- **stack:** laravel
- **type:** skill

## Trigger conditions

- Structuring a new feature (controller, service, action)
- Wiring dependency injection / interface bindings in a service provider
- Designing a consistent JSON API response
- Adding caching, events, or queued jobs to an existing feature

## Content outline

1. **Project structure** — recommended `app/` layout with Actions, Services, and Support layers
2. **Controllers → Services → Actions** — thin controller delegates to Action; Action holds single-purpose logic; DTO bridges Form Request → Action
3. **Service Container bindings** — bind interfaces to implementations in `AppServiceProvider`
4. **Consistent API response shape** — `{success, data, error, meta}` with pagination in `meta`
5. **Query Objects** — encapsulate complex filters as a fluent query object class
6. **Transactions** — `DB::transaction()` for multi-step writes
7. **Caching** — cache expensive reads, invalidate on model events, use tags for related data
8. **Events, Jobs, Queues** — emit domain events for side effects; queue slow work; prefer idempotent handlers

## Cross-references (no duplication)

| Domain | Defers to |
|--------|-----------|
| Eloquent queries, N+1, scopes | `eloquent-query-builder` skill |
| Artisan generators (model, controller, migration) | `artisan-make` skill |
| Pest tests | `pest-test-writer` skill |
| Migration safety | `make-migration` command + `migrations` rule |

## What is NOT in scope

- Eloquent query patterns (owned by `eloquent-query-builder`)
- Migration schema details (owned by `migrations` rule and `make-migration` command)
- Pest test layout (owned by `pest-test-writer`)
- Artisan generator idioms (owned by `artisan-make`)
- Blade templating (owned by `blade` rule)

## Approved approach

Approach B: focused skill that fills gaps and defers to existing artifacts. Avoids drift from duplication; keeps the kit composable.
