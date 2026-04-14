# Installation guide

The Claude PHP & Laravel Kit ships as a collection of copy-paste files: rules, skills, subagents, slash commands, and hooks you drop into your own project. Every file begins with YAML frontmatter, and the `paste-into:` field tells you exactly where that file belongs in the consumer's project tree. Pick the artifacts you want, copy them to the paths below, and commit them alongside your code.

## Where files go

| Artifact type | Project path | Notes |
|---|---|---|
| Rules | `CLAUDE.md` (append) | Or reference via `@php/rules/<file>.md` |
| Skills | `.claude/skills/<name>/SKILL.md` | Copy the whole directory |
| Subagents | `.claude/agents/<name>.md` | |
| Slash commands | `.claude/commands/<name>.md` | |
| Hooks | Merge into `.claude/settings.json` | See the hook file's sibling README |

## Project-level vs user-level

A `.claude/` directory at a project root applies only to that project and can be committed to version control so the whole team shares the same rules, skills, and commands. A `~/.claude/` directory in your home folder applies to every project you open on that machine and stays private to you. Use **project-level** installs for team conventions and project-specific tooling (framework rules, repo-specific subagents, CI hooks); use **user-level** installs for personal preferences and universal utilities you want available everywhere (editor shortcuts, generic helper commands).

## Reading `paste-into:` frontmatter

Every file in the kit starts with a YAML frontmatter block, and the `paste-into:` field names the target path inside the consuming project where that file (or directory) should be placed. Read it before copying so you drop the artifact into the location Claude Code expects.

```yaml
---
name: eloquent-query-builder
description: Enforces eager loading and chunking in Eloquent queries.
paste-into: .claude/skills/eloquent-query-builder/
stack: laravel
type: skill
---
```

The `paste-into:` path is always relative to the consuming project's root directory.
