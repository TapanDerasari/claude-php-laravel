# Three Laravel Skills Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add three new skills to `laravel/skills/` — `laravel-plugin-discovery` (port with MCP setup block), `laravel-security` (rewrite with PHP code examples), and `laravel-verification` (rewrite with shell commands).

**Architecture:** One SKILL.md per skill under `laravel/skills/<name>/`. After all three are written, regenerate the README index and run the full test suite. Skills cross-reference existing kit artifacts (`composer-audit`, `phpstan-runner`, `security` rule) rather than duplicating content.

**Tech Stack:** PHP 8.x, Laravel 11.x/12.x, Markdown, PHP CLI scripts for linting (`scripts/lint-frontmatter.php`) and index generation (`scripts/generate-index.php`).

---

## Background

The kit's validation pipeline:
- `php scripts/lint-frontmatter.php <path>` — validates YAML frontmatter fields (name, description, paste-into, stack, type, author all required)
- `php scripts/generate-index.php` — regenerates the `<!-- INDEX:START -->` table in `README.md`
- `php scripts/generate-index.php --check` — exits non-zero if README is out of date
- `php scripts/tests/lint-frontmatter.test.php` — unit tests for the linter
- `php scripts/tests/generate-index.test.php` — unit tests for the index generator

Existing artifacts to reference (do not duplicate):
- `laravel/rules/security.md` — mass assignment, authz, CSRF, rate limits, secrets, `composer audit`
- `php/skills/composer-audit/SKILL.md` — dependency audit skill
- `php/skills/phpstan-runner/SKILL.md` — static analysis skill

---

### Task 1: Create laravel-plugin-discovery skill

**Files:**
- Create: `laravel/skills/laravel-plugin-discovery/SKILL.md`

**Step 1: Create the file**

Create `laravel/skills/laravel-plugin-discovery/SKILL.md` with this exact content:

```markdown
---
name: laravel-plugin-discovery
description: Discover and evaluate Laravel packages by health, version compatibility, and vendor reputation via the LaraPlugins.io MCP server.
paste-into: .claude/skills/laravel-plugin-discovery/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Plugin Discovery

Find and evaluate Laravel packages using the LaraPlugins.io MCP server — no API key required.

## Prerequisites: MCP server setup

Add the following to `~/.claude.json` (create the file if it doesn't exist):

```json
{
  "mcpServers": {
    "laraplugins": {
      "type": "http",
      "url": "https://laraplugins.io/mcp/plugins"
    }
  }
}
```

Restart Claude Code after adding this. The `SearchPluginTool` and `GetPluginDetailsTool` tools will only be available once the MCP server is active. This skill will not work without it.

## When to use

- Finding packages for authentication, permissions, admin panels, or other common features.
- Verifying a package is actively maintained before adding it to a project.
- Confirming Laravel/PHP version compatibility before `composer require`.
- Evaluating vendor reputation and package health.

## Key tools

### SearchPluginTool

Locate packages using keywords and filters:

- `keywords` — what the package does (e.g., `"permission"`, `"admin panel"`, `"audit log"`)
- `health_score` — `"Healthy"`, `"Moderate"`, or `"Unhealthy"`
- `laravel_version` — e.g., `"11"`, `"12"`
- `php_version` — e.g., `"8.2"`, `"8.3"`

### GetPluginDetailsTool

Retrieve detailed metrics, documentation links, and version history for a specific package by its slug.

## Smart filtering strategy

Always filter by `health_score: Healthy` for production projects. Match `laravel_version` to your project — Laravel 11 and 12 are current; versions 5–9 are end-of-life and unlikely to receive security patches. Combine filters: searching `"permission"` with health and version constraints yields targeted results.

Prefer established vendors like Spatie and Laravel LLC when available.

## What NOT to do

- Do not recommend packages with `health_score: Unhealthy` for production use without flagging the risk to the user.
- Do not skip the `laravel_version` filter — a package without Laravel 11+ support will cause dependency conflicts.
- Do not run this skill without the MCP server configured — `SearchPluginTool` and `GetPluginDetailsTool` will not be available.
```

**Step 2: Lint the frontmatter**

```bash
php scripts/lint-frontmatter.php laravel/skills/laravel-plugin-discovery
```

Expected: exit 0, no output.

**Step 3: Commit**

```bash
git add laravel/skills/laravel-plugin-discovery/SKILL.md
git commit -m "feat(laravel): add laravel-plugin-discovery skill"
```

---

### Task 2: Create laravel-security skill

**Files:**
- Create: `laravel/skills/laravel-security/SKILL.md`

**Step 1: Create the file**

Create `laravel/skills/laravel-security/SKILL.md` with this exact content:

```markdown
---
name: laravel-security
description: Sanctum token patterns, file upload safety, security headers middleware, CORS config, and log redaction for Laravel APIs.
paste-into: .claude/skills/laravel-security/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Security

Implementation-time security patterns for Laravel APIs and web apps. Complements the `security` rule — that rule covers mass assignment, authz, CSRF, rate limits, and secrets (paste it into `CLAUDE.md` first). This skill covers the gaps.

## When to use

- Implementing authentication with Sanctum or Passport.
- Handling file uploads in a controller or action.
- Adding security headers to an API or web application.
- Configuring CORS for an API consumed by a browser.
- Logging request or response data without leaking secrets.

## Sanctum token patterns

Issue short-lived tokens with scoped abilities. Revoke on logout:

```php
// Issue token on login — scope to specific abilities, expire after 30 days
$token = $user->createToken(
    name: 'mobile-app',
    abilities: ['orders:read', 'orders:write'],
    expiresAt: now()->addDays(30),
);

return response()->json(['token' => $token->plainTextToken]);
```

```php
// Revoke the current token on logout
$request->user()->currentAccessToken()->delete();
```

```php
// Revoke all tokens — use on password change or account compromise
$request->user()->tokens()->delete();
```

Protect routes with `auth:sanctum` and ability checks:

```php
Route::middleware(['auth:sanctum', 'abilities:orders:read'])->group(function () {
    Route::get('/orders', [OrdersController::class, 'index']);
});
```

## File upload safety

Validate MIME type and size server-side; store outside the public directory:

```php
// In your Form Request
public function rules(): array
{
    return [
        'document' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'], // 10 MB
    ];
}
```

```php
// In your controller or action — store privately, never under public/
$path = $request->file('document')->store('documents', 'private');
```

- Never store user-uploaded files under `public/` or `storage/app/public/` unless you have verified they cannot be executed by the web server.
- Validate MIME type server-side. The client's `Content-Type` header can be forged.
- Generate a UUID filename for storage so the original name is never used as a path component.

## Security headers middleware

Create a middleware that injects hardened headers on every response:

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'"
        );

        return $response;
    }
}
```

Register globally in `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

Adjust the CSP `script-src` and `style-src` directives to match what your app actually loads. Start strict and loosen as needed — not the reverse.

## CORS config

Lock down `config/cors.php` for API endpoints:

```php
return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins'          => [env('FRONTEND_URL', 'https://app.example.com')],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,
];
```

- Never set `allowed_origins` to `['*']` on routes that use `auth:sanctum` or session cookies — browsers will block credentialed requests to wildcard origins anyway, and the misconfiguration signals intent.
- Set `supports_credentials: true` only when the frontend sends cookies (SPA with Sanctum cookie-based auth).
- Drive `FRONTEND_URL` from an environment variable so it differs between local, staging, and production.

## Log redaction

Never log passwords, tokens, or sensitive fields. Use Laravel's built-in mechanisms:

```php
// In your model — $hidden prevents these fields appearing in toArray() / toJson() / logs
protected $hidden = ['password', 'remember_token', 'api_token'];
```

Never log raw request data:

```php
// BAD — logs passwords, tokens, anything in the payload
Log::info('Request', $request->all());

// GOOD — log only what you need
Log::info('Order placed', ['order_id' => $order->id, 'user_id' => $user->id]);
```

If you must log request data for debugging, explicitly exclude sensitive keys:

```php
Log::debug('Request payload', $request->except(['password', 'token', 'secret', 'api_key', 'card_number']));
```
```

**Step 2: Lint the frontmatter**

```bash
php scripts/lint-frontmatter.php laravel/skills/laravel-security
```

Expected: exit 0, no output.

**Step 3: Commit**

```bash
git add laravel/skills/laravel-security/SKILL.md
git commit -m "feat(laravel): add laravel-security skill"
```

---

### Task 3: Create laravel-verification skill

**Files:**
- Create: `laravel/skills/laravel-verification/SKILL.md`

**Step 1: Create the file**

Create `laravel/skills/laravel-verification/SKILL.md` with this exact content:

```markdown
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

Expected: `No files need formatting.` If Pint reports changed files, run `./vendor/bin/pint` (no `--test`) and commit the result before continuing.

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

Expected: all tests pass, exit 0.

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
grep -c "function up"   database/migrations/*.php
grep -c "function down" database/migrations/*.php
```

Counts should match. A migration missing `down()` cannot be rolled back in an incident.

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
ls -la storage/
ls -la bootstrap/cache/
```

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
```

**Step 2: Lint the frontmatter**

```bash
php scripts/lint-frontmatter.php laravel/skills/laravel-verification
```

Expected: exit 0, no output.

**Step 3: Commit**

```bash
git add laravel/skills/laravel-verification/SKILL.md
git commit -m "feat(laravel): add laravel-verification skill"
```

---

### Task 4: Regenerate the README index

**Files:**
- Modify: `README.md` (auto-updated by script)

**Step 1: Run the index generator**

```bash
php scripts/generate-index.php
```

Expected output: `Updated README.md`

**Step 2: Verify the index is correct**

```bash
php scripts/generate-index.php --check
```

Expected: `README index is up to date.`

Confirm three new rows appear in the `README.md` table:

```
| laravel | skill | laravel-plugin-discovery | Discover and evaluate Laravel packages... | `.claude/skills/laravel-plugin-discovery/` |
| laravel | skill | laravel-security | Sanctum token patterns, file upload safety... | `.claude/skills/laravel-security/` |
| laravel | skill | laravel-verification | Seven-phase pre-PR and pre-deployment... | `.claude/skills/laravel-verification/` |
```

**Step 3: Commit**

```bash
git add README.md
git commit -m "chore: regenerate README index for three new laravel skills"
```

---

### Task 5: Run the full test suite

**Files:** none modified

**Step 1: Run lint tests**

```bash
php scripts/tests/lint-frontmatter.test.php
```

Expected: all cases `PASS`, exit 0.

**Step 2: Run index generator tests**

```bash
php scripts/tests/generate-index.test.php
```

Expected: all cases `PASS`, exit 0.

**Step 3: If any test fails**

- Lint failure → check frontmatter in the relevant `SKILL.md`. All six fields are required: `name`, `description`, `paste-into`, `stack`, `type`, `author`.
- Index failure → re-run `php scripts/generate-index.php` and commit the updated `README.md`.
