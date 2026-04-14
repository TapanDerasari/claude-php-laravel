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
| php | rule | coding-style | PSR-12 formatting, strict types, and naming conventions for PHP code. | `CLAUDE.md` |
| php | rule | hooks | When to run Composer, PHPStan, and formatters — and how to wire them as Claude Code hooks. | `CLAUDE.md` |
| php | rule | patterns | SOLID principles, dependency injection, value objects, and composition for PHP. | `CLAUDE.md` |
| php | rule | security | Input validation, prepared statements, escaping, hashing, and secret handling for PHP. | `CLAUDE.md` |
| php | rule | testing | PHPUnit conventions, AAA structure, and fixture hygiene for PHP projects. | `CLAUDE.md` |
<!-- INDEX:END -->

## Documentation

- [Installation guide](./docs/installation.md) — where each artifact type goes
- [Contributing](./CONTRIBUTING.md) — how to add new content
- [Attributions](./ATTRIBUTIONS.md) — credits to upstream sources

## License

MIT. See [LICENSE](./LICENSE).
