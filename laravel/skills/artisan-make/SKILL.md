---
name: artisan-make
description: Use `php artisan make:*` generators idiomatically instead of hand-writing Laravel scaffolding.
paste-into: .claude/skills/artisan-make/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Artisan Make

Use `php artisan make:*` generators before hand-writing any Laravel scaffolding. Generators produce correctly-wired classes with the right namespaces, base classes, and stub boilerplate.

## When to use

- Creating a new model, controller, migration, request, policy, test, or factory.
- Before writing a PHP file in `app/`, `database/`, or `tests/` by hand.
- When a user asks for a "new X" where X is a Laravel primitive.

## Common generators

- `php artisan make:controller FooController --resource --requests` — resource methods (`index`, `store`, `show`, `update`, `destroy`) plus paired `StoreFooRequest` and `UpdateFooRequest`.
- `php artisan make:model Foo -mfsc` — model plus migration (`-m`), factory (`-f`), seeder (`-s`), and controller (`-c`). Drop flags you don't need.
- `php artisan make:migration create_foos_table` for new tables; `php artisan make:migration add_status_to_foos_table --table=foos` for column changes. Name clearly — Laravel derives the action from the name.
- `php artisan make:request StoreFooRequest` — form request with `authorize()` and `rules()`.
- `php artisan make:policy FooPolicy --model=Foo` — generates policy stubs already type-hinted against the model.
- `php artisan make:test FooTest` for a feature test; add `--unit` for unit tests and `--pest` if the project uses Pest.

## Idioms

- Don't hand-write files that a generator can produce — you'll miss namespace, base class, or trait wiring.
- Chain flags on `make:model` (`-mfsc`) to generate the full feature slice in one command.
- Use `--requests` with `make:controller --resource` so validation lives in form requests, not the controller.
- Pair `make:policy --model=X` with `Gate::resource` or route model binding so authz is wired automatically.

## What to check after generation

- Open the generated file — stubs are scaffolding, not finished code. Fill in `fillable`, `casts`, `rules()`, relationships, and assertions.
- Check the migration `up()` and `down()` columns match what the feature actually needs.
- Confirm the controller was registered in `routes/web.php` or `routes/api.php` (generators do not add routes).
- Run `php artisan migrate` in a test DB before committing migration changes.
