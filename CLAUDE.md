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
- **Agent Shared References**: @.claude/agents/shared-references.md
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

## Project Structure (DDD/Hexagonal Architecture)
```
src/                    # Business contexts (DDD)
├── [Context]Context/   # e.g., BlogContext
│   ├── Application/    # Use cases and gateways
│   ├── Domain/        # Business logic (pure PHP)
│   ├── Infrastructure/# External adapters
│   └── UI/           # User interfaces
```

## Testing Structure
- Tests in `tests/` directory mirroring `src/` structure
- PHPUnit for unit/integration tests
- Behat for functional/acceptance tests

## Development Workflows

For detailed methodology and workflows, see `.claude/CLAUDE.md` which contains:
- Simplified agent-driven development methodology
- Streamlined command workflow (spec → orchestrate → qa)
- Expert agents for implementation (TDD, API, Admin UI)
- Automatic quality assurance