---
name: eloquent-query-builder
description: Write safe, performant Eloquent queries with eager loading, chunking, scopes, and no N+1 traps.
paste-into: .claude/skills/eloquent-query-builder/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Eloquent Query Builder

Write Eloquent queries that are safe on production data volumes: eager-load relationships, chunk large reads, and lean on scopes and failing finders.

## When to use

- Writing any query that fetches multiple rows or crosses a relationship.
- Reviewing code that touches `Model::where(...)`, `->get()`, or `->all()`.
- Investigating slow endpoints or N+1 log warnings.

## Required patterns

- Eager-load relationships with `->with('relation')` or `->with(['author', 'comments.user'])`. Never rely on lazy loading inside a loop.
- Chunk large result sets: `Model::query()->where(...)->chunk(1000, fn ($rows) => ...)` or `->lazy()` for cursor-based iteration.
- Put reused filters in query scopes so call sites read like `Post::published()->latest()->paginate()` instead of repeated `where` chains.
- Use `firstOrFail()` / `findOrFail()` instead of manual null checks — Laravel converts the exception to a 404.
- Use `whereHas` / `whereDoesntHave` for relationship filters, but audit nested `whereHas` for subquery cost.
- Always call `->select([...])` when you only need a few columns from a wide table.

## Anti-patterns to avoid

- `Model::all()` on any table that grows. Always scope, paginate, or chunk.
- `->get()` inside a `foreach` loop — batch with a single `whereIn` or eager load instead.
- Accessing `$model->relation` inside a loop without `->with()` — classic N+1.
- Building queries from raw user input without `where(..., '=', $value)` binding.
- `->count()` followed by `->get()` on the same query — call `->get()` once and `count()` the collection.

## Examples

Eager load + scope:
```php
$posts = Post::published()
    ->with(['author:id,name', 'tags'])
    ->latest()
    ->paginate(20);
```

Chunked write job:
```php
User::query()
    ->where('last_seen_at', '<', now()->subYear())
    ->chunkById(500, fn ($users) => $users->each->archive());
```

Failing finder in a controller:
```php
$post = Post::with('comments')->findOrFail($id);
```
