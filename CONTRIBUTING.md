# Contributing

Thanks for your interest in contributing! The flow is simple: open a pull
request adding a new file (or directory, for skills) under the appropriate
stack (`php/` or `laravel/`) with the required frontmatter. CI will lint the
frontmatter and verify that the README contents index is up to date — if it
isn't, regenerate it with `scripts/generate-index.php` and commit the result.

## Required frontmatter fields

Every contributed file must declare the following YAML frontmatter fields at
the top:

- `name` — short identifier for the artifact
- `description` — one-line summary of what it does
- `paste-into` — the path (relative to the user's project root) where this
  file should be copied
- `stack` — one of `php`, `laravel`
- `type` — one of `rule`, `skill`, `agent`, `command`, `hook`
- Attribution, one of:
  - `source` + `license` — for curated content adapted from an upstream
    project
  - `author` — for wholly original content

## Notes

- [`docs/installation.md`](./docs/installation.md) is the canonical reference
  for valid `paste-into:` targets per artifact type. Consult it before
  choosing a path.
- Skills are directory-based: each skill lives at
  `<stack>/skills/<name>/SKILL.md`, and any supporting files sit alongside
  `SKILL.md` in the same directory.
