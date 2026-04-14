---
name: hooks
description: When to run Composer, PHPStan, and formatters — and how to wire them as Claude Code hooks.
paste-into: CLAUDE.md
stack: php
type: rule
source: affaan-m/everything-claude-code (rules/php/hooks.md)
license: MIT
---

# PHP Hooks (usage rules)

This rule documents **when** to run common PHP tooling during a Claude Code session. For concrete hook configs, see `laravel/hooks/` once that directory ships.

## Composer

- Run `composer install` after pulling, after switching branches, and whenever `composer.lock` changes. It is reproducible and safe.
- Run `composer update` **only** when you intend to bump dependencies. It rewrites `composer.lock`.
- Run `composer update <vendor/package>` to update a single package — never `composer update` blindly inside a feature branch.
- Run `composer audit` before merging dependency changes.

## Static analysis

- Run **PHPStan** (or **Psalm**) after any edit that adds, changes, or removes type hints, return types, or generics.
- Run it scoped to the changed files first (`vendor/bin/phpstan analyse path/to/changed/file.php`) for fast feedback, then the full suite before committing.
- Treat new PHPStan errors as build failures, not warnings.

## Formatters

- Run **php-cs-fixer** or **Laravel Pint** on every edited file before committing.
- Configure the formatter to run only on changed files in the hook, not the whole repo — it is faster and keeps diffs clean.

## Tests

- Run targeted PHPUnit/Pest tests for the files you touched on every save.
- Run the full suite before committing or opening a PR.

## Warnings to flag

- `var_dump`, `print_r`, `dd`, `dump`, `die`, or `exit` left in edited files.
- Newly introduced raw SQL concatenation.
- Edits that disable CSRF, session protection, or output escaping.

## Wiring as Claude Code hooks

Configure PostToolUse hooks in `~/.claude/settings.json` (or project `.claude/settings.json`) to run formatters and analysers automatically after Claude edits a `.php` file. Concrete examples live under `laravel/hooks/` in this kit.
