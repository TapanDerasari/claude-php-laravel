---
name: pest-test-writer
description: Scaffold a Pest test for a given Laravel class with the right layout, fakes, and meaningful assertions.
paste-into: .claude/skills/pest-test-writer/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Pest Test Writer

Scaffold a Pest test for a Laravel class. Pick the right test type (feature vs unit), wire the right fakes, and assert behaviour — not internals.

## When to use

- A new controller, action, service, or job has just been created.
- The user asks for "tests for X" or "a Pest test for X".
- Before modifying existing behaviour that has no test coverage.

## Test file layout

- Controllers and anything touching HTTP, DB, or the container: `tests/Feature/<Name>Test.php` with `uses(RefreshDatabase::class)`.
- Pure classes (services, actions, value objects): `tests/Unit/<Name>Test.php`, no database.
- Group related cases with `describe()` when a file grows past a handful of tests.

## Idioms

- Name controller tests with behaviour: `it('creates a post when authenticated')`, `it('returns 403 for guests')`.
- Name unit tests with inputs and outputs: `test('returns null for missing key')`.
- Put shared setup in `beforeEach(fn () => ...)` — a fresh user, a fresh service instance.
- Use `Http::fake()` for any external HTTP call. Never hit real third-party APIs from a test.
- Use `Event::fake()`, `Queue::fake()`, `Mail::fake()`, `Notification::fake()` to assert side effects without executing them, then `assertDispatched` / `assertPushed` / `assertSent`.
- Use `actingAs($user)` for authenticated routes; `$this->get/post/put/delete(route('...'))` over hard-coded URLs.

## What to assert

- Prefer observable state: `assertDatabaseHas('posts', ['title' => 'Foo'])` over `assertTrue($user->fresh()->posts()->count() === 1)`.
- Assert response status and redirect/JSON shape: `$response->assertCreated()->assertJsonPath('data.id', $post->id)`.
- For fakes, assert the exact event/job/mail was dispatched with the right payload.
- Avoid over-mocking Eloquent — a feature test against a real test DB catches more bugs than a wall of mocks.
- One behaviour per test. If the description needs "and", split it.
