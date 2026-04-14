---
name: migrations
description: Migration safety rules for reversible, low-risk schema changes.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Migration Rules

Every migration ships to production exactly once. Treat merged migrations as immutable history.

## Immutability

- **Never** modify a migration after it's been merged to main — add a new migration instead.
- Never rename a migration file once merged; Laravel tracks them by filename.
- If a bad migration is already in production, write a compensating migration that fixes state forward.

## Reversibility

- Always implement `down()` so `migrate:rollback` works in local and staging.
- For destructive changes (drop column, drop table), `down()` should restore the column/table shape — even if data is lost.
- If a change is genuinely irreversible, throw in `down()` with a clear message: `throw new \RuntimeException('irreversible');`.

## Foreign keys

- Use `->constrained()` for FK definitions so Laravel infers the referenced table.
- Always specify delete behavior explicitly: `->cascadeOnDelete()`, `->restrictOnDelete()`, or `->nullOnDelete()`.
- **Index every foreign key column** — `->constrained()` adds the FK constraint but you still want the composite indexes your queries need.

## Additive changes first

- Prefer additive migrations: add column, backfill, then (in a later deploy) flip NOT NULL or drop the old column.
- Splitting destructive work across two deploys avoids broken-deploy windows where old app code meets new schema.
- Never add a `NOT NULL` column to an existing populated table without a `->default(...)` or a backfill migration.

## Naming

- Descriptive verbs: `create_users_table`, `add_email_verified_at_to_users_table`, `drop_legacy_stats_table`.
- One logical change per migration — don't bundle "add column + rename table + drop index" into one file.

## Writing the migration

- Use the anonymous-class form (`return new class extends Migration { ... };`) — it's the Laravel 11+ default.
- Don't call models from inside migrations; use the query builder (`DB::table('users')`) so future model changes don't break old migrations.
- Keep data backfills in separate migrations (or jobs) from schema migrations so you can retry them independently.
