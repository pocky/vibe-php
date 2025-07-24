# Vibe-PHP Standard Architecture Reference

This repository contains the standard architecture, patterns, and development workflows for all Vibe-PHP projects.

## 1. Core Principles

- **DDD/Hexagonal Architecture**: All projects follow Domain-Driven Design and Hexagonal Architecture. See `@docs/architecture-reference/architecture/principles/ddd-principles.md`.
- **Coding Standards**: We adhere to PSR-12 and use modern PHP 8.4+ features. See `@docs/architecture-reference/architecture/standards/php-features-best-practices.md`.

## 2. Development Workflows

- **TDD**: Test-Driven Development is mandatory. See `@docs/architecture-reference/development/testing/tdd-implementation-guide.md`.
- **Git Workflow**: All commits must be semantic. See `@docs/architecture-reference/development/workflows/git-workflow.md`.
- **Database Migrations**: Follow the entity-first approach. See `@docs/architecture-reference/development/workflows/database-migration-workflow.md`.

## 3. Core Patterns

- **Gateway Pattern**: `@docs/architecture-reference/architecture/patterns/gateway-pattern.md`
- **CQRS Pattern**: `@docs/architecture-reference/architecture/patterns/cqrs-pattern.md`
- **Domain Layer**: `@docs/architecture-reference/architecture/patterns/domain-layer-pattern.md`
- **Generator Pattern**: `@docs/architecture-reference/architecture/patterns/generator-pattern.md`

## 4. Examples

- **Gateway Usage**: `@docs/architecture-reference/development/examples/gateway-generator-usage.md`
- **Value Objects**: `@docs/architecture-reference/development/examples/value-object-creation.md`
