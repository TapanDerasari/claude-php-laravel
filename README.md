# Claude PHP & Laravel Kit

A curated collection of Claude Code rules, skills, subagents, slash commands,
and hooks for PHP and Laravel projects. Copy any file into your project at
the path shown in its `paste-into:` frontmatter and you're done.

## Quick start

1. Browse [`php/`](./php) or [`laravel/`](./laravel).
2. Open any file and read its `paste-into:` frontmatter.
3. Copy the file (or directory, for skills) to that path in your project.

## Contents

<!-- INDEX:START -->
| Stack | Type | Name | Description | Paste into |
|-------|------|------|-------------|------------|
| laravel | agent | laravel-code-reviewer | Reviews Laravel changes for framework conventions, thin controllers, and correct use of Eloquent/Form Requests/Policies. | `.claude/agents/laravel-code-reviewer.md` |
| laravel | agent | migration-safety-reviewer | Reviews Laravel migrations for production safety — additive-only changes, FK indexes, and rollback coverage. | `.claude/agents/migration-safety-reviewer.md` |
| laravel | command | make-migration | Scaffold a Laravel migration with safe defaults, proper rollback, and FK constraints. | `.claude/commands/make-migration.md` |
| laravel | command | write-pest-test | Scaffold a Pest test for a given Laravel class with the right layout and meaningful assertions. | `.claude/commands/write-pest-test.md` |
| laravel | rule | blade | Blade templating rules for safe escaping, components, and thin views. | `CLAUDE.md` |
| laravel | rule | coding-style | Laravel naming, folder conventions, and thin-controller guidance. | `CLAUDE.md` |
| laravel | rule | eloquent | Eloquent best practices for query safety, performance, and idempotency. | `CLAUDE.md` |
| laravel | rule | migrations | Migration safety rules for reversible, low-risk schema changes. | `CLAUDE.md` |
| laravel | rule | security | Laravel security rules for mass assignment, authz, CSRF, and secrets. | `CLAUDE.md` |
| laravel | rule | testing-pest | Pest testing conventions for Laravel feature and unit tests. | `CLAUDE.md` |
| laravel | skill | artisan-make | Use `php artisan make:*` generators idiomatically instead of hand-writing Laravel scaffolding. | `.claude/skills/artisan-make/` |
| laravel | skill | eloquent-query-builder | Write safe, performant Eloquent queries with eager loading, chunking, scopes, and no N+1 traps. | `.claude/skills/eloquent-query-builder/` |
| laravel | skill | pest-test-writer | Scaffold a Pest test for a given Laravel class with the right layout, fakes, and meaningful assertions. | `.claude/skills/pest-test-writer/` |
| php | agent | php-code-reviewer | Reviews plain PHP code for PSR-12 style, security issues, and SOLID violations. | `.claude/agents/php-code-reviewer.md` |
| php | rule | coding-style | PSR-12 formatting, strict types, and naming conventions for PHP code. | `CLAUDE.md` |
| php | rule | hooks | When to run Composer, PHPStan, and formatters — and how to wire them as Claude Code hooks. | `CLAUDE.md` |
| php | rule | patterns | SOLID principles, dependency injection, value objects, and composition for PHP. | `CLAUDE.md` |
| php | rule | security | Input validation, prepared statements, escaping, hashing, and secret handling for PHP. | `CLAUDE.md` |
| php | rule | testing | PHPUnit conventions, AAA structure, and fixture hygiene for PHP projects. | `CLAUDE.md` |
| php | skill | composer-audit | Run `composer audit` after any dependency change and report security advisories. | `.claude/skills/composer-audit/` |
| php | skill | phpstan-runner | Run PHPStan static analysis after editing PHP files and surface errors to the user. | `.claude/skills/phpstan-runner/` |
<!-- INDEX:END -->

## Documentation

- [Installation guide](./docs/installation.md) — where each artifact type goes
- [Contributing](./CONTRIBUTING.md) — how to add new content
- [Attributions](./ATTRIBUTIONS.md) — credits to upstream sources

## License

MIT. See [LICENSE](./LICENSE).
