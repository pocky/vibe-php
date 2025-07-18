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

## 🚀 Quick Start Guide

```mermaid
graph TB
    subgraph "🎯 Quick Commands"
        QS1["/understand<br/>📋 Analyze codebase"]
        QS2["/prd<br/>🎯 Define requirements"]
        QS3["/plan<br/>📐 Design solution"]
        QS4["/act<br/>⚡ Implement TDD"]
        QS5["/qa<br/>✅ Verify quality"]
        QS6["/workflow-status<br/>📊 Check progress"]
    end
    
    subgraph "📚 Essential Docs"
        D1["🧭 Navigation Guide<br/>@docs/agent/instructions/documentation-navigation.md"]
        D2["🏗️ Architecture<br/>@docs/agent/instructions/architecture.md"]
        D3["🔧 Patterns<br/>@docs/reference/"]
        D4["🧪 Testing<br/>@docs/testing/"]
        D5["💡 Examples<br/>@docs/examples/"]
        D6["🔍 Pattern Recognition<br/>@docs/reference/pattern-recognition-guide.md"]
    end
    
    subgraph "📦 Business Contexts"
        C1["📝 Blog<br/>@docs/contexts/blog/"]
        C2["🔐 Security<br/>@docs/contexts/security/"]
        C3["💰 Billing<br/>@docs/contexts/billing/"]
    end
    
    QS1 --> D1
    QS2 --> C1
    QS2 --> C2
    QS2 --> C3
    QS3 --> D2
    QS4 --> D6
    QS4 --> D3
    QS4 --> D4
    QS5 --> D5
    
    style QS1 fill:#e1f5fe
    style QS2 fill:#fff3e0
    style QS3 fill:#f3e5f5
    style QS4 fill:#e8f5e9
    style QS5 fill:#ffebee
    style QS6 fill:#fce4ec
    style D6 fill:#fff9c4
```

## License

This project is licensed under the European Union Public Licence v1.2 (EUPL-1.2). See the [LICENSE](LICENSE) file for details.

## Instructions

### 🧭 Navigation & Getting Started
- **Start here**: Documentation navigation guide in @docs/agent/instructions/documentation-navigation.md
- **Quick reference**: Check the visual guide above for common tasks

### 📖 Core Instructions
- Follow global instructions in @docs/agent/instructions/global.md
- Follow cognitive preservation principles in @docs/agent/instructions/cognitive-preservation.md
- Follow Git workflow standards in @docs/agent/instructions/git-workflow.md
- Follow PR management standards in @docs/agent/instructions/pr-management.md
- Follow QA tools standards in @docs/agent/instructions/qa-tools.md

### 🛠️ Technical Guidelines
- Follow Docker best practices in @docs/agent/instructions/docker.md
- Follow Symfony best practices in @docs/agent/instructions/symfony.md
- Follow architecture patterns in @docs/agent/instructions/architecture.md
- Follow Doctrine Migrations standards in @docs/agent/instructions/doctrine-migrations.md
- Follow API Platform integration patterns in @docs/agent/instructions/api-platform-integration.md

### 📚 References & Resources
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

### 🚨 CRITICAL: Test-Driven Development is MANDATORY

**ALL code in this project MUST be built using Test-Driven Development (TDD). No exceptions.**
- Write failing tests FIRST, then implement code to make them pass
- Direct implementation without tests is STRICTLY FORBIDDEN
- See the "Mandatory TDD Protocol" section below for detailed requirements

## Mandatory TDD Protocol for All Code Development

**For ALL code development, Test-Driven Development is MANDATORY**. No exceptions.

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
- "TDD is mandatory. Should I stop after each phase for your validation?"
- "What specific testing approach do you prefer for this feature?"
- "Are there any special testing considerations I should be aware of?"

### 4. Plan Structure Requirements
All `/act` plans MUST explicitly include:
- **Phase 1: RED** - Detailed list of failing tests to write
- **Phase 2: GREEN** - Minimal implementation steps
- **Phase 3: REFACTOR** - Code improvement steps
- **Validation Points** - Where to pause for developer confirmation

### 5. Never Skip Tests
- **NEVER write implementation code without failing tests first**
- **ALL code must be built using TDD - no exceptions**
- **Direct implementation bypassing TDD is strictly prohibited**
- NEVER use `exit_plan_mode` without TDD phases in the plan
- ALWAYS write tests that fail for the right reasons
- Implementation must be driven by making tests pass

This ensures developer skill preservation and prevents over-automation.

## Mandatory Development Workflow

### Continuous Quality Assurance

When implementing ANY feature or fixing ANY bug, you MUST:

1. **Follow the Mandatory TDD Protocol** for ALL code development
   - TDD is the ONLY acceptable approach for building code
   - Use the `/act` command as it enforces proper TDD workflow
   - Direct implementation without tests is FORBIDDEN

2. **Run QA continuously during development**:
   ```bash
   # After EVERY significant change
   docker compose exec app composer qa
   
   # NOT just at the end of implementation
   ```

3. **QA validation rules**:
   - **NEVER** consider a task complete if ANY QA check fails
   - **NEVER** mark a todo as "completed" if QA is failing
   - **ALWAYS** fix QA errors immediately before continuing
   - **NEVER** commit code that fails QA checks

4. **Development cycle**:
   ```
   Write test → Run QA → Implement → Run QA → Refactor → Run QA → Commit
   ```

### QA Failure Protocol

When QA fails:
1. **STOP** current implementation
2. **FIX** the QA issues immediately
3. **VERIFY** all QA passes before continuing
4. **ONLY THEN** proceed with next task

### Example Workflow

```bash
# ❌ WRONG: Implement everything then check QA at the end
implement_feature()
implement_another_feature()
run_qa()  # Too late!

# ✅ CORRECT: Check QA continuously
write_test()
run_qa()
implement_minimal_code()
run_qa()
refactor()
run_qa()
commit()
```

This ensures code quality is maintained throughout development, not just checked at the end.

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

### Shared Code Restrictions
- **CRITICAL**: The `src/Shared/` and `tests/Shared/` directories contain core framework code
- **NEVER** modify any file in `src/Shared/` or `tests/Shared/` unless explicitly requested by the user
- **ALWAYS** ask for confirmation before making changes to `src/Shared/` or `tests/Shared/`
- The `src/Shared/` directory includes:
  - Gateway infrastructure (DefaultGateway, middleware, etc.)
  - Message Bus interfaces and implementations
  - Core abstractions (Specifications, Generators, etc.)
  - Infrastructure utilities (Slugger, Paginator, etc.)
- The `tests/Shared/` directory includes:
  - Base test classes and abstractions (AbstractIndexPage, AbstractCreatePage, etc.)
  - Common Behat contexts and services
  - Shared testing utilities and helpers
- When implementing features, use the existing Shared components, don't modify them

### Testing Structure
- Tests should be in the `tests/` directory
- The structure of `tests/` should mirror that of `src/`
- Use PHPUnit for testing (needs to be installed)
