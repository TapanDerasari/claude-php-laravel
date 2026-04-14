---
name: make-migration
description: Scaffold a Laravel migration with safe defaults, proper rollback, and FK constraints.
paste-into: .claude/commands/make-migration.md
stack: laravel
type: command
author: claude-php-laravel-kit
---

Generate a Laravel migration for: $ARGUMENTS

Requirements:

1. Use `php artisan make:migration` to create the file with a descriptive name:
   - `create_<table>_table` for new tables
   - `add_<column>_to_<table>_table` for adding columns
   - `remove_<column>_from_<table>_table` for removals

2. Fill in the `up()` method with the schema change requested.

3. Write a matching `down()` method that fully reverses `up()`. Empty `down()` methods are not acceptable.

4. For foreign keys, use the modern constrained syntax with an explicit delete behavior:
   ```php
   $table->foreignId('user_id')->constrained()->cascadeOnDelete();
   ```
   Pick `cascadeOnDelete()`, `restrictOnDelete()`, or `nullOnDelete()` based on the business rule — do not leave it implicit.

5. Index every foreign key column (Laravel's `->constrained()` does this automatically; raw `->foreign()` does not).

6. For `NOT NULL` columns on existing tables, either provide a default or split the work into a multi-step migration (add nullable → backfill → enforce).

7. After writing the migration, show the file path and a summary of what it does. Do not run the migration automatically — leave that to the user.

If `$ARGUMENTS` is ambiguous (e.g., "add timestamps to users"), ask one clarifying question before generating.
