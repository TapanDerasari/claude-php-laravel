---
name: blade
description: Blade templating rules for safe escaping, components, and thin views.
paste-into: CLAUDE.md
stack: laravel
type: rule
author: claude-php-laravel-kit
---

# Blade Rules

Templates are for rendering, not deciding. Keep logic out and escaping on.

## Escaping

- Always use `{{ $var }}` for user-controlled output — it's HTML-escaped.
- Use `{!! $html !!}` **only** for trusted HTML (markdown rendered server-side, editor output already sanitized).
- Every `{!! !!}` must have a one-line comment above it explaining why it's safe.
- Never interpolate user input into `<script>` blocks; use `@json($data)` so it's properly JSON-escaped.

## Components over includes

- Prefer Blade components (`<x-card>`, `<x-forms.input>`) over `@include` for reusable UI.
- Components get typed props, slots, and better error messages.
- Use anonymous components (`resources/views/components/*.blade.php`) for pure markup; class-based components when you need PHP logic.

## No business logic

- No database queries in templates. No `User::where(...)->get()` in a view.
- No arithmetic or formatting beyond trivial cases — push to the controller, a **view model**, or a **view composer**.
- Avoid `@php ... @endphp` blocks. If you need one, the template is doing too much.

## Forms

- `@csrf` on every non-GET form (POST / PUT / PATCH / DELETE).
- Use `@method('PUT')` / `@method('DELETE')` for method spoofing on forms.
- Pair inputs with `old('field', $model->field)` so validation failures preserve state.
- Use `@error('field') ... @enderror` blocks for per-field error display.

## Authorization in views

- Use `@can('update', $post)` / `@cannot(...)` / `@canany([...])` for authz gates in templates.
- Don't manually check `$user->role === 'admin'` — go through a Gate or Policy so the rule lives in one place.

## Layouts

- Use a single root layout component (`<x-layouts.app>`) with slots rather than `@extends` + `@section` for new code.
- Keep `<head>` and nav in the layout; pages only render their own content.
