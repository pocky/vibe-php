# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Configuration Notes

### PHP Configuration Format
- **ALL configuration in this project uses PHP files**, not YAML
- Configuration directory: `@config/` contains PHP files exclusively
- **NEVER** look for `.yml` or `.yaml` files in this project
- Examples:
  - `config/services.php` (not services.yaml)
  - `config/packages/*.php` (not *.yaml)
  - `config/routes.php` (not routes.yaml)

### Behat Configuration
- The Behat configuration file is `behat.dist.php` (NOT `behat.yml` or `behat.yaml`)
- This uses PHP configuration format with the Behat Config objects
- Suite configurations are imported from `config/behat/suites.php`

## License

This project is licensed under the European Union Public Licence v1.2 (EUPL-1.2). See the [LICENSE](LICENSE) file for details.

## Instructions

### 🌍 Language Policy
- **ALL documentation, code comments, and technical artifacts MUST be written in English**
- User conversations can be in any language, but generated files are ALWAYS in English
- This includes: PRDs, technical designs, user stories, test scenarios, commit messages, and code

### 🧭 Navigation & Getting Started
- **Start here**: Documentation navigation guide in @docs/reference/agent/instructions/documentation-navigation.md
- **Architecture Reference**: All conventions and patterns in @docs/reference/

### 📖 Core Instructions
- Follow global instructions in @docs/reference/agent/instructions/global.md
- Follow cognitive preservation principles in @docs/reference/agent/instructions/cognitive-preservation.md
- Follow Git workflow standards in @docs/reference/agent/instructions/git-workflow.md
- Follow PR management standards in @docs/reference/agent/instructions/pr-management.md
- Follow QA tools standards in @docs/reference/development/tools/qa-tools.md

### 🛠️ Technical Guidelines
- Follow Docker best practices in @docs/reference/agent/instructions/docker.md
- Follow Symfony best practices in @docs/reference/agent/instructions/symfony.md
- Follow architecture patterns in @docs/reference/agent/instructions/architecture.md
- Follow Doctrine Migrations standards in @docs/reference/agent/instructions/doctrine-migrations.md
- Follow API Platform integration patterns in @docs/reference/agent/instructions/api-platform-integration.md

### 📚 References & Resources
- Reference implementation patterns in @docs/reference/architecture/patterns/ for specific patterns
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
├── BlogContext/        # Blog bounded context  
│   ├── Application/    # Use cases and gateways
│   ├── Domain/        # Business logic
│   ├── Infrastructure/# External adapters
│   └── UI/           # User interfaces
└── Kernel.php        # Application kernel

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

## Coding Standards and Best Practices

### PHP Coding Standards
- Follow PSR-4 autoloading standard (MANDATORY)
- Follow PSR-12 coding standard
- Use strict typing: `declare(strict_types=1);` at the beginning of each PHP file
- Naming conventions:
  - PascalCase for classes and interfaces
  - camelCase for methods and variables
  - UPPER_CASE_SNAKE_CASE for constants
- Classes should be `final` by default
- Use `private` visibility by default for properties and methods

### Project Structure (DDD/Hexagonal Architecture)
- Organize code by business contexts following Domain-Driven Design
- **Never** create Controller/, Entity/, Repository/ directories at the root of src/
- **Always** organize code within context directories:
  ```
  src/
  ├── BlogContext/
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

### Modern PHP Features
- Use PHP 8.4+ features: property hooks, asymmetric visibility, readonly classes
- Use PHP 8.3+ features: typed properties, constructor property promotion, attributes, #[\Override]
- Prefer immutability when possible (use readonly keyword)
- See @docs/reference/architecture/standards/php-features-best-practices.md for comprehensive guidelines

## Error Handling

- Follow error handling protocol in @docs/reference/agent/instructions/error-handling.md
- Maximum 3 attempts for any failing operation
- Document persistent errors in @docs/reference/agent/errors.md

## Testing Structure
- Tests should be in the `tests/` directory
- The structure of `tests/` should mirror that of `src/`
- Use PHPUnit for testing

## Development Workflows

For detailed methodology and workflows, see `.claude/CLAUDE.md` which contains:
- Spec-driven development methodology
- Command-driven workflow with slash commands
- TDD implementation guidelines
- Quality assurance requirements