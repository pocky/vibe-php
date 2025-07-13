# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## License

This project is licensed under the European Union Public Licence v1.2 (EUPL-1.2). See the [LICENSE](LICENSE) file for details.

## Instructions

- Follow global instructions in @docs/agent/instructions/global.md
- Follow cognitive preservation principles in @docs/agent/instructions/cognitive-preservation.md
- Follow Git workflow standards in @docs/agent/instructions/git-workflow.md
- Follow PR management standards in @docs/agent/instructions/pr-management.md
- Follow QA tools standards in @docs/agent/instructions/qa-tools.md
- Follow Docker best practices in @docs/agent/instructions/docker.md
- Follow Symfony best practices in @docs/agent/instructions/symfony.md
- Follow architecture patterns in @docs/agent/instructions/architecture.md
- Follow Doctrine Migrations standards in @docs/agent/instructions/doctrine-migrations.md
- Follow API Platform integration patterns in @docs/agent/instructions/api-platform-integration.md
- Reference implementation patterns in @docs/reference/ for specific patterns
- External documentation references in @docs/reference/external-docs.md
- Available commands are in @composer.json scripts section

## Project Overview

PHP 8.4+ application with Domain-Driven Design structure running in Docker.

### Key Components
- **Framework**: Symfony 7.3
- **Architecture**: Domain-Driven Design with bounded contexts
- **Environment**: Docker (development and test services)
- **Development URL**: http://localhost (port 80)

### Directory Structure
```
src/                    # Business contexts (DDD)
├── ExampleContext/     # Example bounded context
│   └── UI/Controller/  # Controllers for this context
└── Kernel.php         # Application kernel

etc/docker/           # Docker configurations
├── entrypoints/      # Container entrypoints
└── php/conf.d/       # PHP configurations
```

## Development Environment

- **Services**: `app` (dev) and `app_test` (test environment)
- **Xdebug**: Disabled by default, enable with `XDEBUG_MODE=debug`
- **Web Profiler**: Available in dev environment with toolbar enabled
- **Profiler**: Collects performance data and debug information

## Current Status

- ✅ **Testing**: PHPUnit 12.2 configured and integrated
- ✅ **Code Quality**: ECS, PHPStan, Rector, Twig CS Fixer integrated
- ✅ **Development Tools**: Web Profiler, Debug Bundle available
- ✅ **Database**: Doctrine ORM with migrations strategy
- ✅ **Architecture**: DDD with CQRS and Gateway patterns
- ℹ️ **Dependencies**: Uses custom mformono packages

## AI Agent Best Practices

When working with AI agents in this codebase, follow the two-step approach documented in `docs/ai-agent-best-practices.md`:

1. **Suggestion Phase**: Use a reasoning model to analyze and plan
2. **Implementation Phase**: Use a coding model to execute the plan

This separation ensures better control, higher quality results, and easier debugging.

## TDD Protocol for /act Command

When using the `/act` command, **MANDATORY Test-Driven Development approach**:

### 1. Strict Red-Green-Refactor Cycle
- **RED Phase**: Write failing tests FIRST, implementation comes AFTER
- **GREEN Phase**: Write minimal code to make tests pass
- **REFACTOR Phase**: Improve code while keeping tests green

### 2. Implementation Flow
```bash
# Red phase - ALWAYS FIRST
git commit -m "test: add failing test for [feature]"

# Green phase - MINIMAL implementation
git commit -m "feat: implement [feature] to pass tests"

# Refactor phase - improve without changing behavior
git commit -m "refactor: improve [aspect] while keeping tests green"
```

### 3. Cognitive Preservation Questions
Before using `exit_plan_mode`, ALWAYS ask:
- "Do you want me to follow strict TDD Red-Green-Refactor cycles?"
- "Should I stop after each phase for your validation?"
- "What specific testing approach do you prefer for this feature?"

### 4. Plan Structure Requirements
All `/act` plans MUST explicitly include:
- **Phase 1: RED** - Detailed list of failing tests to write
- **Phase 2: GREEN** - Minimal implementation steps
- **Phase 3: REFACTOR** - Code improvement steps
- **Validation Points** - Where to pause for developer confirmation

### 5. Never Skip Tests
- NEVER implement code before writing failing tests
- NEVER use `exit_plan_mode` without TDD phases in the plan
- ALWAYS write tests that fail for the right reasons
- Implementation must be driven by making tests pass

This ensures developer skill preservation and prevents over-automation.

## Error Handling

- Follow error handling protocol in @docs/agent/instructions/error-handling.md
- Maximum 3 attempts for any failing operation
- Document persistent errors in @docs/agent/errors.md

## Coding Standards and Best Practices

### PHP Coding Standards
- Follow PSR-12 coding standard
- Use strict typing: `declare(strict_types=1);` at the beginning of each PHP file
- Naming conventions:
  - PascalCase for classes and interfaces
  - camelCase for methods and variables
  - UPPER_CASE_SNAKE_CASE for constants
- Classes should be `final` by default
- Use `private` visibility by default for properties and methods

### Modern PHP Features
- Use PHP 8.4+ features: property hooks, asymmetric visibility, readonly classes
- Use PHP 8.3+ features: typed properties, constructor property promotion, attributes, #[\Override]
- Prefer immutability when possible (use readonly keyword)
- See @docs/reference/php-features-best-practices.md for comprehensive guidelines

### Project Structure (DDD/Hexagonal Architecture)
- Organize code by business contexts following Domain-Driven Design
- **Never** create Controller/, Entity/, Repository/ directories at the root of src/
- **Always** organize code within context directories:
  ```
  src/
  ├── UserContext/
  │   ├── Application/
  │   ├── Domain/
  │   ├── Infrastructure/
  │   └── UI/
  └── BillingContext/
      ├── Application/
      ├── Domain/
      ├── Infrastructure/
      └── UI/
  ```

### Testing Structure
- Tests should be in the `tests/` directory
- The structure of `tests/` should mirror that of `src/`
- Use PHPUnit for testing (needs to be installed)
