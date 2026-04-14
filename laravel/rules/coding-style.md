---
name: coding-style
description: Laravel naming, folder conventions, and thin-controller guidance.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Laravel Coding Style

Targets Laravel 11.x / 12.x idioms. Keep code aligned with framework conventions so artisan generators and route caching keep working.

## Naming

- **Controllers**: PascalCase, always suffixed `Controller` (`OrderController`, `UserProfileController`).
- **Models**: singular PascalCase (`User`, `Order`, `InvoiceLine`).
- **Tables**: plural snake_case (`users`, `orders`, `invoice_lines`).
- **Pivot tables**: alphabetical singular snake_case (`role_user`, not `user_role`).
- **Migrations**: `YYYY_MM_DD_HHMMSS_verb_noun` (e.g. `create_users_table`, `add_email_verified_at_to_users_table`).
- **Routes**: kebab-case URLs, snake_case route names (`users.password_reset`).
- **Views**: kebab-case filenames (`resources/views/orders/show.blade.php`).

## Folder layout

- `app/Http/Controllers/` — thin HTTP adapters only.
- `app/Http/Requests/` — every non-trivial form uses a Form Request for validation + authorization.
- `app/Services/` — domain orchestration that spans multiple models.
- `app/Actions/` — single-purpose invokable classes (`CreateOrder`, `CancelSubscription`) when using the action pattern.
- `app/Policies/` — one policy per model, registered in `AuthServiceProvider`.
- `app/Models/` — Eloquent models only; no query-building helpers that belong in a repository or scope.

## Controllers

- Keep controllers thin: resolve input, delegate to a Service or Action, return a response.
- Prefer **resource controllers** (`Route::resource`) for CRUD; only break out custom routes when the resource verbs don't fit.
- Use `__invoke` for single-action controllers (`class PublishPostController { public function __invoke(...) }`).
- Inject dependencies via the constructor or method; don't call `app()` / facades inside business logic.
- Return typed responses (`JsonResponse`, `RedirectResponse`, `View`) so static analysis can help.

## General

- Run `./vendor/bin/pint` before committing — it's Laravel's opinionated formatter and the default in new projects.
- One class per file, namespace matches PSR-4 autoload in `composer.json`.
- Prefer constructor property promotion and readonly properties where the class is effectively a DTO.
