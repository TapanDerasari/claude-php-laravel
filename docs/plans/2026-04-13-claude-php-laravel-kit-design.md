# Claude PHP & Laravel Kit — Design

**Date:** 2026-04-13
**Status:** Approved, ready for implementation planning

## Purpose

A curated GitHub repository that serves as a single point of visit for Claude
Code rules, skills, subagents, slash commands, and hooks targeted at PHP and
Laravel developers. Developers browse the repo, copy any file into their own
project at the path indicated by the file's frontmatter, and immediately gain
the behavior described.

The repo is inspired by `affaan-m/everything-claude-code`, which organizes
language-specific guidance in folders like `rules/php/`. This kit narrows the
focus to PHP and Laravel so a developer working in that stack finds everything
they need in one place.

## Scope

- **In scope:** PHP-generic and Laravel-specific rules, skills, subagents,
  slash commands, and hook snippets. Curated from existing open sources with
  attribution.
- **Out of scope (v1):** Other languages, framework plugins (Symfony, CakePHP,
  etc.), a CLI installer, a Claude Code plugin manifest, package publishing,
  and versioning discipline. These may come later.

## Non-goals

- Not a runtime tool. There is no code to execute, no build step, no package
  to install.
- Not an opinionated "framework" for Claude Code. Files are independent and
  can be adopted individually.
- Not a replacement for `affaan-m/everything-claude-code`. It is a focused,
  curated subset for the PHP/Laravel stack.

## Architecture

### Repository layout

```
claude-php-laravel-kit/
├── README.md                  Project overview + auto-generated contents index
├── LICENSE                    MIT
├── CONTRIBUTING.md            How to add new content
├── ATTRIBUTIONS.md            Credits to upstream sources
├── docs/
│   ├── installation.md        Where each artifact type goes in a project
│   └── plans/                 Design docs (including this one)
├── scripts/
│   └── generate-index.*       README index generator (language TBD in plan)
├── .github/workflows/
│   └── ci.yml                 Frontmatter lint + index drift check
├── php/
│   ├── README.md
│   ├── rules/
│   ├── skills/
│   ├── agents/
│   ├── commands/
│   └── hooks/
└── laravel/
    ├── README.md
    ├── rules/
    ├── skills/
    ├── agents/
    ├── commands/
    └── hooks/
```

Two top-level stacks (`php/` and `laravel/`) keep framework-specific and
framework-agnostic content separate. A developer browsing `laravel/` sees
everything Laravel in one tree.

### Naming conventions

- **Rules:** topic-based, kebab-case markdown. Example: `security.md`,
  `eloquent.md`.
- **Skills:** each skill is a directory containing at least `SKILL.md`, so
  helper files can live beside it. Example:
  `laravel/skills/eloquent-query-builder/SKILL.md`.
- **Subagents:** single markdown files. Example:
  `laravel/agents/laravel-code-reviewer.md`.
- **Slash commands:** single markdown files. Example:
  `laravel/commands/make-migration.md`.
- **Hooks:** JSON snippets plus a sibling `README.md` per folder explaining
  how to merge into `.claude/settings.json`.

### File format: frontmatter contract

Every file (rules, skills, agents, commands, hook READMEs) carries YAML
frontmatter with at least:

```yaml
---
name: <artifact name>
description: <one-line purpose, used in the auto-generated index>
paste-into: <target path in the consuming project>
source: <upstream repo and path, if curated>  # or `author:` for original work
license: <upstream license, if curated>
stack: php|laravel
type: rule|skill|agent|command|hook
---
```

The `paste-into:` field is the central contract. A developer reading any file
in the repo can see exactly where it goes without consulting external docs.

### File templates

**Rule** — plain markdown body after frontmatter. Topic-organized bullets and
short sections. Expected to be pasted into `CLAUDE.md` or referenced from it.

**Skill** — `SKILL.md` follows Claude Code's standard skill format
(frontmatter with `name` and `description`, then body). Lives in a directory
to allow bundled reference files.

**Subagent** — frontmatter with `name`, `description`, and `tools`, then the
system prompt body.

**Slash command** — frontmatter with `name` and `description`, then the
prompt template using `$ARGUMENTS`.

**Hook** — a JSON file containing a `settings.json`-compatible snippet plus a
sibling markdown README with merge instructions and frontmatter.

## Installation model

Pure manual copy-paste. No installer, no CLI, no plugin manifest. The
installation story for a developer is:

1. Browse the repo on GitHub.
2. Open a file you want.
3. Read its `paste-into:` frontmatter.
4. Copy the file (or directory, for skills) to that path in your own project.

`docs/installation.md` provides the canonical table of target paths per
artifact type, plus a short explanation of project-level (`.claude/`) vs
user-level (`~/.claude/`) installation.

## v1 content scope

About 25 files, curated and adapted from upstream sources.

**`php/rules/` (5):** `coding-style.md`, `security.md`, `testing.md`,
`patterns.md`, `hooks.md`.

**`php/skills/` (2):** `phpstan-runner/`, `composer-audit/`.

**`php/agents/` (1):** `php-code-reviewer.md`.

**`laravel/rules/` (6):** `coding-style.md`, `eloquent.md`, `migrations.md`,
`blade.md`, `testing-pest.md`, `security.md`.

**`laravel/skills/` (3):** `artisan-make/`, `eloquent-query-builder/`,
`pest-test-writer/`.

**`laravel/agents/` (2):** `laravel-code-reviewer.md`,
`migration-safety-reviewer.md`.

**`laravel/commands/` (2):** `make-migration.md`, `write-pest-test.md`.

**`laravel/hooks/` (2):** `pre-commit-pint.json`, `post-edit-phpstan.json`.

Everything beyond v1 is a future PR.

## README and docs

**Top-level `README.md`** opens with a 2-sentence elevator pitch, a 3-bullet
quick start, then an auto-generated contents table between
`<!-- INDEX:START -->` and `<!-- INDEX:END -->` markers. The tail of the
README links to installation, contributing, license, and attributions.

**Per-stack `README.md`** (`php/README.md`, `laravel/README.md`) lists the
contents of that stack as a short table for quick browsing.

**`docs/installation.md`** is the canonical reference for where each artifact
type is installed in a consuming project.

### Auto-generated index

`scripts/generate-index.*` walks `php/` and `laravel/`, reads each file's
frontmatter, and rewrites the contents table in the top-level README between
the index markers. Runs in CI on every PR. A `--check` mode fails CI if the
committed README differs from freshly generated output, so the index cannot
drift.

The script language (Node, PHP, or Bash) is a decision for the implementation
plan.

## Attribution and licensing

- **Repo license:** MIT.
- **Attribution file:** `ATTRIBUTIONS.md` at the root lists every upstream
  source with its repo URL, license, which files were adapted, and a short
  note on what changed.
- **Per-file attribution:** curated files include `source:` and `license:` in
  their frontmatter so attribution travels with the file.
- **License compatibility:** before merging any curated file, its upstream
  license must be verified compatible with MIT redistribution. Incompatible
  files are not included.

## Maintenance workflow

Documented in `CONTRIBUTING.md`:

1. New content arrives as PRs.
2. Every new file must include `paste-into:` and either `source:` (if
   curated) or `author:` (if original).
3. CI runs the index generator in `--check` mode and fails on drift.
4. CI runs a schema check on frontmatter (all required fields present).
5. For skills: `SKILL.md` must have valid `name` and `description` fields.

## CI

One GitHub Actions workflow at `.github/workflows/ci.yml` with two jobs:

- **`lint-frontmatter`** — walks every `.md` file and asserts that required
  frontmatter fields are present for that artifact type.
- **`check-index`** — runs `scripts/generate-index.*` in check mode and fails
  if the README index differs from generated output.

A third optional job, **`test-skills`**, can be added later to validate
`SKILL.md` files against Claude Code's skill schema when that schema is
stable.

## Failure modes

The only realistic failure mode is frontmatter drift — a file missing
`paste-into:` or an outdated index. CI catches both. There is no runtime to
fail, no dependency chain to break, and no users who can "install the wrong
version" because the repo is just files.

## Decisions deferred to implementation plan

- Language of the index generator script (Node, PHP, Bash).
- Exact frontmatter schema for each artifact type (required vs optional
  fields).
- Initial curation sources beyond `affaan-m/everything-claude-code`.
- Whether hook READMEs need their own frontmatter or inherit from the JSON
  file.
