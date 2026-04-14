---
name: patterns
description: SOLID principles, dependency injection, value objects, and composition for PHP.
paste-into: CLAUDE.md
stack: php
type: rule
source: affaan-m/everything-claude-code (rules/php/patterns.md)
license: MIT
---

# PHP Patterns

## SOLID, briefly

- **S**ingle responsibility: a class has one reason to change.
- **O**pen/closed: extend behavior without editing stable code.
- **L**iskov: subtypes must be substitutable for their base type without surprises.
- **I**nterface segregation: many small interfaces beat one fat one.
- **D**ependency inversion: depend on abstractions, not concretions.

## Dependency injection

- Inject collaborators through the **constructor**. No service locator lookups, no `new` inside business logic.
- Depend on interfaces or narrow contracts, not framework globals.
- Constructors should assign and validate — no real work, no I/O.

## Value objects

- Wrap constrained domain concepts (Money, EmailAddress, UserId, DateRange) in value objects.
- Make them **immutable** and self-validating in the constructor.
- Use `readonly` properties. Override `equals()` or rely on structural comparison for identity.

## Early returns

- Prefer early returns (guard clauses) over nested `if`/`else` pyramids.
- Validate preconditions at the top of the method and return or throw immediately.
- Keep the happy path at the lowest indentation level.

## Composition over inheritance

- Reach for composition first. Inheritance is for **is-a** relationships, not code reuse.
- Favor small traits or injected collaborators over deep class hierarchies.
- Avoid static **god classes** and singletons. They are untestable and hide dependencies.

## Boundaries

- Isolate ORM models from domain logic once models start doing more than persistence.
- Wrap third-party SDKs behind small adapters so the rest of the codebase depends on your contract, not theirs.
- Convert framework/request input into validated DTOs before it reaches domain services.
