---
name: security
description: Laravel security rules for mass assignment, authz, CSRF, and secrets.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Laravel Security Rules

Laravel gives you safe defaults. These rules keep them on.

## Mass assignment

- Always define `$fillable` on every Eloquent model. Never use `$guarded = []` on a user-writable model.
- Never pass `$request->all()` into `Model::create()` or `->update()` unless you also filtered via a Form Request.
- Prefer `$request->validated()` — it returns only validated keys.

## Input validation

- Every user input goes through a **Form Request** (`app/Http/Requests/`). Inline `$request->validate([...])` is OK for tiny controllers, but Form Requests scale better.
- Form Requests do both validation **and** authorization (`authorize()` method).
- Validate types, not just presence (`integer`, `uuid`, `in:a,b,c`, `exists:users,id`).
- Don't trust route model binding alone to enforce ownership — add a policy check.

## Authorization

- Every sensitive action goes through a **Gate** or **Policy**. Register policies in `AuthServiceProvider`.
- Never inline checks like `if ($user->id === $post->user_id)` — that rule must live in `PostPolicy::update()` so it's testable and reused.
- Use `$this->authorize('update', $post)` in controllers, `@can` in Blade, `Gate::check()` in background code.

## CSRF and sessions

- `@csrf` on every non-GET form — Laravel enforces this via `VerifyCsrfToken` middleware.
- Don't add routes to `$except` in `VerifyCsrfToken` unless they're genuine webhook endpoints (and then verify signatures).
- Regenerate the session on login (`$request->session()->regenerate()`) to prevent session fixation.

## Signed URLs and rate limits

- Use `URL::signedRoute('unsubscribe', [...])` for one-off sensitive links (password reset, email unsubscribe, action confirmations).
- Apply `throttle:` middleware on auth routes (`login`, `register`, `password.*`) and on write-heavy endpoints.
- Configure rate limiters in `RouteServiceProvider::configureRateLimiting()` — don't scatter magic numbers across routes.

## Secrets

- Never expose `.env` in responses, logs, or error pages. `APP_DEBUG=false` in production, always.
- Access secrets via `config('services.stripe.secret')`. Call `env()` **only** inside `config/*.php` files — outside config, `env()` returns `null` once config is cached.
- Rotate any secret that ever appears in a commit, log, or screenshot.

## Dependencies

- Run `composer audit` in CI on every PR. Treat advisories as blockers.
- Keep Laravel itself on the latest patch of a supported major (11.x or 12.x).
