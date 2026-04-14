---
name: testing-pest
description: Pest testing conventions for Laravel feature and unit tests.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Pest Testing Rules

Targets Pest 3 on Laravel 11.x / 12.x. Pest is the default test runner in new Laravel projects.

## Test shape

- Use Pest's `it('does a thing', fn () => ...)` or `test('it does a thing', ...)` with descriptive names — the name is the spec.
- Group related tests with `describe('OrderController', function () { ... })` so failures are easy to scan.
- `beforeEach()` for per-test setup (fresh user, seeded config). Avoid class-level or file-level mutable state.
- Use `dataset()` for parametrized cases instead of hand-rolled loops.

## Database

- Use the `RefreshDatabase` trait (`uses(RefreshDatabase::class)`) for any feature test that touches the DB.
- Prefer `RefreshDatabase` over `DatabaseTransactions` — it handles schema changes between runs.
- Use **factories** (`User::factory()->create()`) for all test data. Don't hand-build models with raw arrays.
- Assert DB state with `assertDatabaseHas('users', [...])` and `assertDatabaseMissing(...)`, not by re-querying and comparing.

## Feature vs unit

- **Feature tests** (`tests/Feature`) hit HTTP routes via `$this->get('/orders')`, `->post(...)`, `->actingAs($user)`. Use them for anything that flows through a controller.
- **Unit tests** (`tests/Unit`) cover pure logic — services, actions, value objects. No HTTP, no DB if avoidable.
- Don't mock Eloquent models in feature tests. Hit the real test DB — mocking the ORM hides real bugs.

## External services

- `Http::fake()` for outbound HTTP calls. Never hit real third-party APIs from tests.
- `Mail::fake()`, `Queue::fake()`, `Event::fake()`, `Storage::fake('s3')` for their respective facades.
- `Notification::fake()` + `assertSentTo()` for notifications.

## Running tests

- `./vendor/bin/pest` or `php artisan test` — both work.
- `./vendor/bin/pest --parallel` for speed once tests are hermetic.
- `./vendor/bin/pest --filter=OrderTest` to narrow during debugging.
- Keep the suite green on main; a red test is a production bug waiting to happen.
