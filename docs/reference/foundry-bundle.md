# ZenstruckFoundryBundle Documentation Reference

## Overview
Foundry provides a flexible fixture generation system for Symfony and Doctrine with type-safe and dynamic test data creation.

## Official Documentation
- **Main Documentation**: https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html
- **Stories Section**: https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories

## Directory Structure

### 1. Factories (`src/Factory/` or `tests/Factory/`)
- **Purpose**: One factory per ORM entity or MongoDB document
- **Generation**: `make:factory` command
- **Responsibilities**:
  - Creating and configuring test objects
  - Generating random data
  - Defining default values
  - Creating relationships between entities

### 2. Stories (`src/Story/` or `tests/Story/`)
- **Purpose**: Define complex database states
- **Generation**: `make:story` command
- **Responsibilities**:
  - Loading multiple objects
  - Establishing relationships
  - Creating realistic test scenarios
  - Can be used in tests, dev fixtures, and other stories

### 3. DataFixtures
- **Purpose**: Local development fixtures
- **Marking**: `#[AsFixture]` attribute
- **Loading**: `bin/console foundry:load-stories` command
- **Organization**: Can be organized into groups

## Key Benefits
- Dynamic fixture creation
- Type-safe test data generation
- Minimal boilerplate code
- Integrated with testing workflow
- Easy relationship management

## Common Commands
```bash
# Generate factory for entity
bin/console make:factory

# Generate story
bin/console make:story

# Load fixtures in development
bin/console foundry:load-stories
```

## Best Practices
- Use factories for individual entity creation
- Use stories for complex scenarios with multiple entities
- Organize fixtures into logical groups
- Keep factories simple and focused
- Use stories to represent realistic business scenarios