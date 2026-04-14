---
name: coding-style
description: PSR-12 formatting, strict types, and naming conventions for PHP code.
paste-into: CLAUDE.md
stack: php
type: rule
source: affaan-m/everything-claude-code (rules/php/coding-style.md)
license: MIT
---

# PHP Coding Style

## Standards

- Follow **PSR-12** for formatting and naming.
- Always start PHP files with `declare(strict_types=1);`.
- One class per file; filename matches classname.

## Naming

- **PascalCase** for classes, interfaces, traits, enums.
- **camelCase** for methods, functions, properties, and local variables.
- **UPPER_SNAKE_CASE** for class constants and global constants.
- Interfaces do not need an `I` prefix; traits do not need a `Trait` suffix unless it disambiguates.

## Types

- Add scalar type hints on every parameter and return type.
- Use typed properties (`private int $count;`) — avoid untyped dynamic properties.
- Prefer `readonly` properties for value objects and immutable DTOs.
- Use `void` for methods that return nothing; do not omit the return type.

## Imports

- Add a `use` statement for every referenced class, interface, trait, function, and constant.
- Group imports: classes first, then `use function`, then `use const`. Alphabetize within groups.
- Avoid relying on the global namespace.

## Error handling

- Throw exceptions for exceptional states. Do not return `false` or `null` as hidden error channels.
- Catch narrow exception types, not bare `\Exception` or `\Throwable`, unless re-throwing.

## Tooling

- Format with **PHP-CS-Fixer** (or **Laravel Pint** where applicable).
- Static analysis with **PHPStan** or **Psalm** at the highest level the project can sustain.
- Keep Composer scripts (`composer lint`, `composer analyse`, `composer test`) checked in so local and CI run the same commands.
