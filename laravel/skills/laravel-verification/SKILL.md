---
name: laravel-verification
description: Seven-phase pre-PR and pre-deployment verification pipeline: environment, autoload, lint, tests, audit, migrations, and cache.
paste-into: .claude/skills/laravel-verification/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Verification

A sequential verification pipeline to run before opening a PR, after major refactors, or before deploying. Each phase must pass before proceeding to the next.

> **Related skills:** Phase 3 uses the `phpstan-runner` skill for output interpretation. Phase 5 uses the `composer-audit` skill for severity guidance.

## When to use

- Before opening a pull request.
- After a large refactor or dependency update.
- Pre-deployment to staging or production.

---

## Phase 1: Environment

Verify runtime versions and required configuration.

```bash
php -v          # must be ≥ 8.2
composer --version
php artisan --version
```

Check `.env`:
- `APP_KEY` must be non-empty.
- `APP_DEBUG=false` for production. `APP_DEBUG=true` is acceptable for local and staging only.
- `DB_CONNECTION` and `DB_DATABASE` must match the target environment.

**Stop if:** `APP_KEY` is missing, or `APP_DEBUG=true` on a production deployment.

---

## Phase 2: Autoload

Rebuild and optimise the Composer autoloader.

```bash
composer dump-autoload -o
```

Expected: no `Warning` or `Class ... not found` lines in the output. If you see class-not-found warnings, a `composer.json` `autoload` path is misconfigured or a file is missing.

---

## Phase 3: Lint and static analysis

Run the formatter in check mode, then PHPStan.

```bash
./vendor/bin/pint --test
```

Exits 0 with no output if nothing needs formatting. Exits non-zero and lists changed files otherwise. If Pint reports changed files, run `./vendor/bin/pint` (no `--test`) and commit the result before continuing.

```bash
./vendor/bin/phpstan analyse --memory-limit=1G --no-progress
```

Expected: `[OK] No errors` or only errors already suppressed in `phpstan.neon`. See the `phpstan-runner` skill for how to interpret and fix PHPStan output.

**Stop if:** PHPStan reports new errors not present before your changes.

---

## Phase 4: Tests

Run the full test suite.

```bash
php artisan test
# or, if the project uses Pest directly:
./vendor/bin/pest
```

For coverage (requires Xdebug):

```bash
XDEBUG_MODE=coverage php artisan test --coverage --min=80
```

Expected: all tests pass. Non-zero exit means failures.

**Stop if:** any test fails. Do not proceed to later phases with a failing suite.

---

## Phase 5: Security audit

Scan dependencies for known vulnerabilities.

```bash
composer audit --format=plain
```

See the `composer-audit` skill for severity guidance. Critical and High advisories are blockers.

**Stop if:** any Critical or High advisory is reported.

---

## Phase 6: Migrations

Preview pending migrations and verify rollback coverage.

```bash
php artisan migrate --pretend
```

Review the SQL output. Confirm no unexpected destructive operations (unintended `DROP COLUMN`, `DROP TABLE`, `TRUNCATE`).

Check that every `up()` has a corresponding `down()`:

```bash
grep -rL "function down" database/migrations/*.php
```

Empty output means every migration has a `down()`. Any filename printed is a migration that cannot be rolled back.

**Stop if:** `--pretend` shows a destructive change you did not intend.

---

## Phase 7: Cache and build

Clear stale cache, then warm it for the target environment.

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Cache for production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Verify storage permissions are writable by the web server user:

```bash
[ -w storage/logs ] && echo "storage/logs writable" || echo "storage/logs NOT WRITABLE"
[ -w bootstrap/cache ] && echo "bootstrap/cache writable" || echo "bootstrap/cache NOT WRITABLE"
```

Both must be writable by the web server user.

---

## Quick reference

| Phase | Local dev | CI | Staging / Prod |
|-------|-----------|----|----------------|
| 1. Environment | optional | required | required |
| 2. Autoload | optional | required | required |
| 3. Lint & static analysis | required | required | — |
| 4. Tests | required | required | — |
| 5. Security audit | on dep change | required | — |
| 6. Migrations | required | required | required |
| 7. Cache & build | — | — | required |

Phase 5 is omitted from the Staging/Prod column because CI is assumed to have run it — if deploying a hotfix that bypasses CI, run `composer audit` manually.
