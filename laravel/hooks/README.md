---
name: laravel-hooks
description: Laravel-specific Claude Code hooks — Pint auto-format before writes and PHPStan after edits.
paste-into: .claude/settings.json (merge the JSON snippets in this folder)
stack: laravel
type: hook
author: claude-php-laravel-kit
---

# Laravel hooks

Each `.json` file in this folder is a snippet you merge into your project's
`.claude/settings.json` under the `hooks` key. They are not standalone settings
files — you must merge them with any hooks you already have.

## Files

- **`pre-commit-pint.json`** — Runs `./vendor/bin/pint --dirty` before any `Edit` or `Write` tool call. Keeps only-changed files formatted, so the formatter does not rewrite untouched files.
- **`post-edit-phpstan.json`** — Runs PHPStan after any `Edit` or `Write` tool call. Surfaces type errors immediately so Claude can fix them in the same turn.

## How to merge

1. Open your project's `.claude/settings.json` (create it if missing).
2. If `hooks` does not exist at the top level, paste the whole snippet as-is.
3. If `hooks` already exists:
   - Find the matching event (`PreToolUse`, `PostToolUse`, etc.) in your existing config.
   - Append the snippet's matcher block to the existing array for that event.
   - If the event does not exist yet, add it as a new key inside `hooks`.

**Do not** blindly copy the outer `{"hooks": {...}}` wrapper when `hooks` already exists in your settings — that will overwrite your existing hooks.

## Prerequisites

- **Pint hook:** `composer require --dev laravel/pint`
- **PHPStan hook:** `composer require --dev phpstan/phpstan` and a `phpstan.neon(.dist)` config at the project root.

## Why `.dirty` for Pint

The `--dirty` flag tells Pint to only format files that have uncommitted changes. Without it, Pint rewrites every PHP file in the project on every edit, creating a huge and noisy diff.
