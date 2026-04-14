---
name: security
description: Input validation, prepared statements, escaping, hashing, and secret handling for PHP.
paste-into: CLAUDE.md
stack: php
type: rule
source: affaan-m/everything-claude-code (rules/php/security.md)
license: MIT
---

# PHP Security

## Database

- Use **prepared statements** for every dynamic query (`PDO::prepare`, Doctrine, any query builder). Never interpolate user input into SQL.
- Bind parameters by type where the driver supports it.
- Never concatenate identifiers (table/column names) from user input; whitelist them against a known set.

## Output escaping

- Escape every value rendered into HTML with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- Treat raw HTML rendering as an exception that must be justified in a comment.
- Use context-specific escaping for JSON (`json_encode` with `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`), URL query strings (`urlencode`/`rawurlencode`), and shell arguments (`escapeshellarg`).

## Passwords and auth

- Hash with `password_hash($plain, PASSWORD_DEFAULT)`. Verify with `password_verify()`.
- Never use `md5`, `sha1`, or unsalted SHA-256 for passwords.
- Regenerate session IDs (`session_regenerate_id(true)`) after login and privilege changes.
- Enforce **CSRF tokens** on every state-changing form and request.

## Secrets and config

- Load secrets from environment variables or a secret manager. Never commit API keys, DB passwords, or tokens to source control.
- Keep `.env` out of git; ship an `.env.example` with placeholder values.
- Run `composer audit` in CI and review new packages before adding them.

## File uploads

- Validate the MIME type with `finfo_file`, not just the client-provided `$_FILES[...]['type']`.
- Validate the file extension against an allow-list.
- **Rename** the uploaded file to a generated name (e.g., UUID) and store outside the web root when possible.
- Reject symlinks and zero-byte files.

## Request input

- Validate every query param, body field, cookie, and header at the boundary before it reaches domain logic.
- Cast and constrain types explicitly — do not trust `$_GET`/`$_POST` shape.
