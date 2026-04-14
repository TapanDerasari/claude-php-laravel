---
name: write-pest-test
description: Scaffold a Pest test for a given Laravel class with the right layout and meaningful assertions.
paste-into: .claude/commands/write-pest-test.md
stack: laravel
type: command
author: claude-php-laravel-kit
---

Write a Pest test for: $ARGUMENTS

Steps:

1. Determine the test type from the target class:
   - Controller or route → **feature test** in `tests/Feature/`
   - Service / Action / plain class → **unit test** in `tests/Unit/`

2. Create the file with `php artisan make:test <Name>Test --pest` (or `--pest --unit` for unit tests).

3. Write the test body:
   - Feature tests use the `RefreshDatabase` trait and hit the real test database.
   - Use `beforeEach()` for shared setup (authed user, fixtures, etc.).
   - Use `it('does the thing when X', function () { ... })` naming.
   - Fake external services: `Http::fake()`, `Queue::fake()`, `Mail::fake()`, `Event::fake()` — never hit real third-party APIs.

4. Cover at minimum:
   - The happy path.
   - One unauthorized / unauthenticated case (if applicable).
   - One validation failure (if the endpoint has input validation).
   - One edge case the target class specifically handles.

5. Use meaningful assertions:
   - Prefer `assertDatabaseHas` / `assertDatabaseMissing` over `assertTrue($model->fresh()->attribute)`.
   - `assertStatus(201)` is clearer than `assertSuccessful` for creation endpoints.
   - For internal behavior, assert on the side effect, not on internal state.

6. Do not mock Eloquent models in feature tests — use the test database.

7. After writing, show the file path and the list of `it(...)` cases.

If `$ARGUMENTS` is ambiguous, ask one clarifying question (e.g., which method of the class the user wants covered first).
