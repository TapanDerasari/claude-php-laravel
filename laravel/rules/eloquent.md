---
name: eloquent
description: Eloquent best practices for query safety, performance, and idempotency.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Eloquent Rules

Applies to Laravel 11.x / 12.x. The defaults below keep queries predictable under load.

## Eager loading

- Eager-load relations with `->with(['user', 'items.product'])` anywhere you iterate results.
- Enable `Model::preventLazyLoading(!app()->isProduction())` in `AppServiceProvider::boot()` so N+1 issues blow up in dev and CI.
- Use `withCount()` instead of `->count()` on loaded collections.

## Result sets

- Never call `->get()` or `->all()` on an unbounded query. Use `->chunk()`, `->chunkById()`, or `->lazy()` for bulk work.
- Use `->cursor()` only when you also control memory downstream — it still loads hydrated models.
- Prefer `->paginate()` (or `->simplePaginate()`) for user-facing lists.

## Query shape

- Move repeated `where` chains into **query scopes** (`scopeActive`, `scopeForTenant`) on the model.
- `firstOrFail()` and `findOrFail()` over `first()` / `find()` + manual 404.
- `updateOrCreate()` and `firstOrCreate()` for idempotent writes (webhook handlers, upserts, sync jobs).
- `DB::transaction(fn () => ...)` when a write touches multiple tables.

## Mass assignment

- Prefer **`$fillable`** (allow-list) over `$guarded = []`. Never ship `$guarded = []` on a user-writable model.
- Keep `$fillable` narrow — exclude `id`, `*_id` foreign keys you don't expose, timestamps, and anything privileged.
- Cast columns with `$casts` (`'is_admin' => 'boolean'`, `'meta' => 'array'`, `'published_at' => 'datetime'`).

## Raw SQL

- Avoid raw SQL unless the query builder truly can't express it.
- If unavoidable, parameter-bind: `DB::select('... where id = ?', [$id])`. Never concatenate user input.
- Wrap raw expressions in `DB::raw()` deliberately and document why.

## Events and observers

- Use model events sparingly — they run on every save and are easy to miss in tests.
- Prefer explicit Service/Action calls for side effects that must be obvious at the callsite.
