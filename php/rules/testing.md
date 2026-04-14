---
name: testing
description: PHPUnit conventions, AAA structure, and fixture hygiene for PHP projects.
paste-into: CLAUDE.md
stack: php
type: rule
source: affaan-m/everything-claude-code (rules/php/testing.md)
license: MIT
---

# PHP Testing

## Framework

- Use **PHPUnit** as the default. If the project is already on **Pest**, prefer Pest for new tests and do not mix frameworks within one suite.
- Put tests under `tests/` mirroring `src/` layout.

## Structure

- Follow the **AAA** pattern: **Arrange** fixtures and collaborators, **Act** on the unit under test, **Assert** the observable outcome.
- One behavior per test. Prefer many small tests over one test with many assertions.
- Name tests after behavior, not implementation: `test_it_rejects_expired_tokens`, `test_it_returns_empty_array_for_unknown_user`.

## Assertions

- Assert on public outcomes, not private state.
- One logical assertion per test. Multiple `assert*` calls are fine if they verify the same concept (e.g., the shape of one returned object).
- Prefer `assertSame` over `assertEquals` unless loose comparison is intentional.

## Test doubles

- Do not mock **value objects** or DTOs — construct them directly.
- Mock at the boundary: HTTP clients, clocks, filesystems, third-party SDKs.
- Prefer fakes and stubs over deep mock expectations. If a test grows long argument-matcher chains, the design is probably wrong.

## Fixtures

- Use factories/builders for test data. Avoid large hand-written associative arrays.
- Keep fixtures minimal: only the fields the test actually cares about.

## Integration vs unit

- **Unit tests** are fast, isolated, and have no I/O. Run them on every save.
- **Integration tests** hit real dependencies at the boundary — database, filesystem, HTTP. Do not stub these; the point is to verify wiring.
- Separate suites so the fast loop stays fast.

## Coverage

- Coverage is a smoke signal, not a goal. Enforce a threshold in CI but do not chase 100%.
- Generate with `vendor/bin/phpunit --coverage-text` using pcov or Xdebug.
