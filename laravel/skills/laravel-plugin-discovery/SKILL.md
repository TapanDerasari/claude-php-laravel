---
name: laravel-plugin-discovery
description: Discover and evaluate Laravel packages by health, version compatibility, and vendor reputation via the LaraPlugins.io MCP server.
paste-into: .claude/skills/laravel-plugin-discovery/
stack: laravel
type: skill
author: claude-php-laravel-kit
---

# Laravel Plugin Discovery

Find and evaluate Laravel packages using the LaraPlugins.io MCP server — no API key required.

## Prerequisites: MCP server setup

Add the following to `~/.claude.json` (create the file if it doesn't exist):

```json
{
  "mcpServers": {
    "laraplugins": {
      "type": "http",
      "url": "https://laraplugins.io/mcp/plugins"
    }
  }
}
```

Restart Claude Code after adding this. The `SearchPluginTool` and `GetPluginDetailsTool` tools will only be available once the MCP server is active. This skill will not work without it.

## When to use

- Finding packages for authentication, permissions, admin panels, or other common features.
- Verifying a package is actively maintained before adding it to a project.
- Confirming Laravel/PHP version compatibility before `composer require`.
- Evaluating vendor reputation and package health.

## Key tools

### SearchPluginTool

Locate packages using keywords and filters:

- `keywords` — what the package does (e.g., `"permission"`, `"admin panel"`, `"audit log"`)
- `health_score` — `"Healthy"`, `"Moderate"`, or `"Unhealthy"`
- `laravel_version` — e.g., `"11"`, `"12"`
- `php_version` — e.g., `"8.2"`, `"8.3"`

### GetPluginDetailsTool

Retrieve detailed metrics, documentation links, and version history for a specific package by its slug.

## Smart filtering strategy

Always filter by `health_score: Healthy` for production projects. Match `laravel_version` to your project — Laravel 11 and 12 are current; versions 5–9 are end-of-life and unlikely to receive security patches. Combine filters: searching `"permission"` with health and version constraints yields targeted results.

Prefer established vendors like Spatie and Laravel LLC when available.

## What NOT to do

- Do not recommend packages with `health_score: Unhealthy` for production use without flagging the risk to the user.
- Do not skip the `laravel_version` filter — a package without Laravel 11+ support will cause dependency conflicts.
- Do not run this skill without the MCP server configured — `SearchPluginTool` and `GetPluginDetailsTool` will not be available.
