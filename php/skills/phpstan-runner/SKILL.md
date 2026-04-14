---
name: phpstan-runner
description: Run PHPStan static analysis after editing PHP files and surface errors to the user.
paste-into: .claude/skills/phpstan-runner/
stack: php
type: skill
author: claude-php-laravel-kit
---

# PHPStan Runner

Run PHPStan static analysis after any PHP edit and surface failures to the user.

## When to use

- After writing or modifying any `.php` file that has type hints.
- Before committing changes that touch typed interfaces or method signatures.
- When a user asks you to "check types" or "run static analysis".

## Prerequisites

- Project has PHPStan installed: `composer require --dev phpstan/phpstan`.
- A `phpstan.neon` or `phpstan.neon.dist` file exists at the project root.

## How to run

1. From the project root, run:
   ```bash
   ./vendor/bin/phpstan analyse --memory-limit=1G --no-progress
   ```
2. If output contains `[ERROR]` or non-zero exit code, parse the file path, line number, and message from each entry.
3. Report each failure to the user with: `<file>:<line> — <message>`.
4. Do not auto-fix errors unless the user asks. Suggest a fix if the cause is obvious.

## What to ignore

- Errors in `vendor/` — those are third-party code.
- Errors matching rules the project has explicitly excluded in `phpstan.neon`.

## Example output format

> `app/Services/UserRepository.php:42 — Method returns mixed, should be User|null`
