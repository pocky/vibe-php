# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Configuration Notes

### PHP Configuration Format
- **ALL configuration in this project uses PHP files**, not YAML
- Configuration directory: `@config/` contains PHP files exclusively
- **NEVER** look for `.yml` or `.yaml` files in this project

### Behat Configuration
- The Behat configuration file is `behat.dist.php` (NOT `behat.yml` or `behat.yaml`)
- Suite configurations are imported from `config/behat/suites.php`

## License

This project is licensed under the European Union Public Licence v1.2 (EUPL-1.2). See the [LICENSE](LICENSE) file for details.

## Instructions

### 🌍 Language Policy
- **ALL documentation, code comments, and technical artifacts MUST be written in English**
- User conversations can be in any language, but generated files are ALWAYS in English

### 📚 Essential References
For all technical guidelines, standards, and patterns, see:
- **Agent Shared References**: @.claude/docs/shared-references.md
- **Documentation Navigation**: @docs/reference/agent/instructions/documentation-navigation.md

## Project Overview

PHP 8.4+ application with Domain-Driven Design structure running in Docker.

### Key Components
- **Framework**: Symfony 7.3
- **Architecture**: Domain-Driven Design with bounded contexts
- **Environment**: Docker (development and test services)
- **Development URL**: http://localhost (port 80)

### Directory Structure
```
src/                         # Business contexts (DDD)
├── Blog/                   # Blog bounded context (simplified naming)
│   ├── Application/        # Use cases and gateways
│   │   ├── Gateway/        # Gateway pattern implementation
│   │   │   └── {Entity}/   # Grouped by entity
│   │   │       └── {Operation}/  # e.g., CreateArticle
│   │   │           ├── Gateway.php
│   │   │           ├── Request.php
│   │   │           ├── Response.php
│   │   │           └── Middleware/
│   │   │               └── Processor.php
│   │   ├── Operation/      # CQRS operations
│   │   │   ├── Command/    # Write operations
│   │   │   │   └── {Entity}/{Command}/
│   │   │   └── Query/      # Read operations
│   │   │       └── {Entity}/{Query}/
│   │   └── Shared/         # Application shared elements
│   │       ├── Generator/  # ID generator interfaces
│   │       └── ReadModel/  # Read models for queries
│   ├── Domain/             # Business logic
│   │   ├── Article/        # Article aggregate
│   │   │   ├── ArticleCreator.php  # Domain service
│   │   │   ├── ArticleUpdater.php  # Domain service
│   │   │   └── Shared/     # Shared article elements
│   │   │       ├── Model/
│   │   │       ├── Repository/     # Repository interfaces (Read/Write separation)
│   │   │       ├── Identifier/
│   │   │       ├── ValueObject/
│   │   │       ├── Event/
│   │   │       └── Exception/
│   │   └── Shared/         # Cross-entity shared
│   │       ├── ValueObject/
│   │       ├── Service/
│   │       └── Exception/
│   ├── Infrastructure/     # External adapters
│   │   ├── Identity/       # ID generators
│   │   ├── Persistence/    # Data persistence
│   │   │   └── Doctrine/
│   │   │       └── ORM/
│   │   │           ├── Entity/         # Doctrine entities
│   │   │           ├── *WriteRepository.php # Write repository implementations
│   │   │           └── *ReadRepository.php  # Read repository implementations
│   │   ├── Service/        # Service implementations
│   │   └── Shared/
│   │       └── Mapper/     # Infrastructure mappers (for read models)
│   └── UI/                # User interfaces
│       ├── API/           # REST API controllers
│       ├── Admin/         # Admin panel controllers
│       └── Front/         # Frontend controllers
└── Kernel.php             # Application kernel

etc/docker/                 # Docker configurations
├── entrypoints/           # Container entrypoints
└── php/conf.d/            # PHP configurations
```

## Development Environment

- **Services**: `app` (dev) and `app_test` (test environment)
- **Xdebug**: Disabled by default, enable with `XDEBUG_MODE=debug`
- **Web Profiler**: Available in dev environment with toolbar enabled
- **Profiler**: Collects performance data and debug information
- **PHPUnit**: Configured with BypassFinals extension for mocking final classes

## Current Status

- ✅ **Testing**: PHPUnit 12.2 with BypassFinals extension
- ✅ **Code Quality**: ECS, PHPStan, Rector, Twig CS Fixer integrated
- ✅ **Development Tools**: Web Profiler, Debug Bundle available
- ✅ **Database**: Doctrine ORM with migrations strategy
- ✅ **Architecture**: DDD with CQRS and Gateway patterns
- ✅ **Repository Pattern**: Read/Write separation with fluent interfaces
- ℹ️ **Dependencies**: Uses custom mformono packages

## Repository Pattern

The project uses a sophisticated repository pattern with Read/Write separation:

### Write Repositories
- Extend `DoctrineRepository` (not `ServiceEntityRepository`)
- Methods: `add()`, `update()`, `remove()`, `get()`, `findById()`
- No more `save()` method - use explicit `add()` or `update()`
- Handle domain aggregates

### Read Repositories
- Return ReadModel instances (DTOs)
- Fluent interface for queries: `->withNameLike()->withLatestFirst()->paginate()`
- Optimized for query performance

## 🚨 CRITICAL: Quality Implementation Standards

**ALL code in this project MUST be built with high quality standards.**
- Follow structured implementation workflows
- Write comprehensive tests to ensure code reliability
- Direct implementation without validation is STRICTLY FORBIDDEN

## Mandatory Development Workflow

### Continuous Quality Assurance

When implementing ANY feature or fixing ANY bug, you MUST:

1. **Run QA continuously during development**:
   ```bash
   # After EVERY significant change
   docker compose exec app composer qa
   
   # NOT just at the end of implementation
   ```

2. **QA validation rules**:
   - **NEVER** consider a task complete if ANY QA check fails
   - **NEVER** mark a todo as "completed" if QA is failing
   - **ALWAYS** fix QA errors immediately before continuing
   - **NEVER** commit code that fails QA checks

3. **Development cycle**:
   ```
   Write test → Run QA → Implement → Run QA → Refactor → Run QA → Commit
   ```

### QA Failure Protocol

When QA fails:
1. **STOP** current implementation
2. **FIX** the QA issues immediately
3. **VERIFY** all QA passes before continuing
4. **ONLY THEN** proceed with next task

## Project Structure (DDD/Hexagonal Architecture)
```
src/                         # Business contexts (DDD)
├── [Context]/               # Bounded contexts (e.g., Blog, User, Payment, Subscription)
│   ├── Application/         # Use cases and gateways
│   │   ├── Gateway/        # Gateway pattern implementation
│   │   │   └── {Entity}/   # Grouped by entity
│   │   │       └── {Operation}/
│   │   │           ├── Gateway.php
│   │   │           ├── Request.php
│   │   │           ├── Response.php
│   │   │           └── Middleware/
│   │   │               └── Processor.php
│   │   ├── Operation/      # CQRS implementation
│   │   │   ├── Command/    # Write operations
│   │   │   │   └── {Entity}/{Command}/
│   │   │   │       ├── Command.php
│   │   │   │       └── Handler.php
│   │   │   └── Query/      # Read operations
│   │   │       └── {Entity}/{Query}/
│   │   │           ├── Query.php
│   │   │           ├── Handler.php
│   │   │           └── View.php
│   │   └── Shared/         # Application layer shared
│   │       ├── Generator/  # ID generator interfaces
│   │       ├── ReadModel/  # Read models for queries
│   │       └── Exception/  # Application exceptions
│   ├── Domain/             # Business logic (pure PHP)
│   │   ├── {Entity}/       # Entity-centric organization
│   │   │   ├── {Entity}Creator.php     # Domain service
│   │   │   ├── {Entity}Updater.php     # Domain service
│   │   │   ├── {Entity}Deleter.php     # Domain service
│   │   │   ├── {Entity}Getter.php      # Domain service
│   │   │   └── Shared/     # Shared within entity
│   │   │       ├── Model/              # Domain model
│   │   │       ├── Repository/         # Repository interfaces (Read/Write separation)
│   │   │       │   ├── {Entity}WriteRepositoryInterface.php
│   │   │       │   └── {Entity}ReadRepositoryInterface.php
│   │   │       ├── Identifier/         # Entity identifiers
│   │   │       ├── ValueObject/        # Entity value objects
│   │   │       ├── Event/              # Domain events
│   │   │       ├── Exception/          # Domain exceptions
│   │   │       └── Specification/      # Business rules
│   │   └── Shared/         # Shared across entities
│   │       ├── ValueObject/            # Shared value objects
│   │       ├── Service/                # Domain service interfaces
│   │       └── Exception/              # Shared exceptions
│   ├── Infrastructure/     # External adapters
│   │   ├── Identity/       # ID generators
│   │   ├── Persistence/    # Data persistence
│   │   │   └── Doctrine/
│   │   │       └── ORM/
│   │   │           ├── Entity/         # Doctrine entities
│   │   │           ├── {Entity}WriteRepository.php # Write operations (extends DoctrineRepository)
│   │   │           └── {Entity}ReadRepository.php  # Read operations (extends DoctrineRepository)
│   │   ├── Service/        # Service implementations
│   │   └── Shared/
│   │       └── Mapper/     # Infrastructure mappers (Entity -> ReadModel)
│   └── UI/                # User interfaces
│       ├── API/           # REST API
│       │   └── Rest/      # RESTful controllers
│       ├── Admin/         # Admin panel
│       ├── Front/         # Frontend controllers
│       └── CLI/           # Console commands
```

## Testing Structure
```
tests/                       # Tests mirror source structure
├── [Context]/              # Bounded contexts (e.g., Blog, User, Payment, Subscription)
│   ├── Unit/               # Unit tests
│   │   ├── Domain/
│   │   │   ├── {Entity}/   # Entity-centric tests
│   │   │   │   ├── {Entity}CreatorTest.php
│   │   │   │   ├── {Entity}UpdaterTest.php
│   │   │   │   └── Shared/ # Model, VO, Event tests
│   │   │   └── Shared/     # Cross-entity tests
│   │   ├── Application/
│   │   │   ├── Gateway/{Entity}/{Operation}/
│   │   │   └── Operation/
│   │   │       ├── Command/{Entity}/{Command}/
│   │   │       └── Query/{Entity}/{Query}/
│   │   └── Infrastructure/
│   │       └── Persistence/
│   │           └── Mapper/     # Test query mappers
│   ├── Integration/        # Integration tests
│   │   └── Infrastructure/
│   │       └── Persistence/
│   │           ├── {Entity}WriteRepositoryTest.php # Test write operations
│   │           └── {Entity}ReadRepositoryTest.php  # Test read operations & fluent interface
│   ├── Functional/         # Functional tests
│   └── Behat/             # BDD acceptance tests
└── Shared/                # Test utilities
```
- PHPUnit for unit/integration tests
- Behat for functional/acceptance tests
- Tests follow same entity-centric organization as source code

## Development Workflows

For detailed methodology and workflows, see `.claude/CLAUDE.md` which contains:
- Simplified agent-driven development methodology
- Streamlined command workflow (spec → orchestrate → qa)
- Expert agents for implementation (TDD, API, Admin UI)
- Automatic quality assurance
