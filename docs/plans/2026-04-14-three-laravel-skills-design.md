# Design: Add laravel-plugin-discovery, laravel-security, laravel-verification Skills

**Date:** 2026-04-14  
**Branch:** feat/v1-kit  
**Approach:** Hybrid (C) — port plugin-discovery as-is, rewrite security and verification in kit style

---

## Context

Three skills from [affaan-m/everything-claude-code](https://github.com/affaan-m/everything-claude-code) are candidates for inclusion:

- `laravel-plugin-discovery` — finds Laravel packages via LaraPlugins.io MCP server
- `laravel-security` — comprehensive security patterns
- `laravel-verification` — pre-PR/pre-deployment verification pipeline

The kit already has `laravel/rules/security.md` (passive CLAUDE.md guidance), `php/skills/composer-audit`, and `php/skills/phpstan-runner`. The three new skills complement rather than duplicate these.

---

## File Placement

All three go under `laravel/skills/` to match existing layout:

```
laravel/skills/laravel-plugin-discovery/SKILL.md
laravel/skills/laravel-security/SKILL.md
laravel/skills/laravel-verification/SKILL.md
```

`paste-into` frontmatter: `.claude/skills/<name>/` in the user's project.  
README index updated via existing `scripts/` tooling.

---

## Skill 1: laravel-plugin-discovery

**Approach:** Port as-is (tool-oriented, no code examples needed).

**Additions over source:**
- Standard kit frontmatter (`stack: laravel`, `type: skill`, `author: claude-php-laravel-kit`)
- MCP setup block at the top — `~/.claude.json` config snippet with note that the skill requires the server to be active
- Short "What NOT to do" section (don't recommend `health_score: Unhealthy` packages; don't skip version compatibility checks)

**Sections:**
- Prerequisites / MCP setup
- Key tools (`SearchPluginTool`, `GetPluginDetailsTool`)
- Primary use cases
- Smart filtering strategy
- What NOT to do

---

## Skill 2: laravel-security

**Approach:** Rewrite in kit style. Complements `laravel/rules/security.md` — that rule covers mass assignment, authz, CSRF, rate limits, and secrets. This skill covers the gaps.

**When to use:** Implementing auth flows, file uploads, or API endpoints; adding security headers; reviewing CORS config.

**Sections with PHP code examples:**

1. **Sanctum token patterns** — issuing short-lived tokens, revoking on logout, `abilities` scoping
2. **File upload safety** — MIME validation, size limits, `Storage::disk('private')` outside `public/`
3. **Security headers middleware** — `SecurityHeaders` middleware class with CSP, HSTS, X-Frame-Options, Referrer-Policy
4. **CORS config** — `config/cors.php` patterns, no wildcards on authenticated routes
5. **Log redaction** — `$hidden` on models, redacting in `report()`, never logging `$request->all()`

---

## Skill 3: laravel-verification

**Approach:** Rewrite in kit style. Orchestrates a sequential verification pipeline with actual shell commands. References existing `composer-audit` and `phpstan-runner` skills rather than duplicating them.

**When to use:** Before opening a PR, after major refactors, pre-deployment.

**7 phases:**

| # | Phase | Key command |
|---|-------|-------------|
| 1 | Environment | `php -v`, `composer --version`, confirm `APP_KEY` set |
| 2 | Autoload | `composer dump-autoload -o` |
| 3 | Lint & static analysis | `./vendor/bin/pint --test` → `./vendor/bin/phpstan analyse` |
| 4 | Tests | `php artisan test` / `./vendor/bin/pest` |
| 5 | Security audit | `composer audit --format=plain` |
| 6 | Migrations | `php artisan migrate --pretend`, verify `down()` exists |
| 7 | Cache & build | `php artisan config:cache && route:cache && view:cache` |

**Quick reference table:** which phases to run for local dev, CI, and staging.

---

## What is NOT changing

- `laravel/rules/security.md` — unchanged, stays as passive CLAUDE.md guidance
- `php/skills/composer-audit` and `php/skills/phpstan-runner` — unchanged, referenced by laravel-verification
- No new agents, commands, or hooks
