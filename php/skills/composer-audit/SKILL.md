---
name: composer-audit
description: Run `composer audit` after any dependency change and report security advisories.
paste-into: .claude/skills/composer-audit/
stack: php
type: skill
author: claude-php-laravel-kit
---

# Composer Audit

Run `composer audit` after any change to `composer.json` or `composer.lock` and surface security advisories.

## When to use

- After editing `composer.json` (adding, removing, or updating a dependency).
- After running `composer install`, `composer update`, or `composer require`.
- Before the user commits a dependency change.

## How to run

1. From the project root:
   ```bash
   composer audit --format=plain
   ```
2. If the command exits non-zero, each advisory is listed with package name, severity, CVE, and a short description.
3. Report every advisory to the user. Do not filter or hide.

## Severity guidance

- **Critical / High:** Stop and require user decision before committing. Suggest upgrading to a patched version if one exists.
- **Medium:** Flag to the user. Recommend upgrade but allow the commit to proceed.
- **Low / Info:** Note once, no action required unless the user asks.

## What NOT to do

- Do not automatically upgrade packages unless the user explicitly asks.
- Do not suppress advisories with `--no-dev` or similar flags.
