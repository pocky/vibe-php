---
description: Create a DDD entity with value objects and tests
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# DDD Entity Creation

Create a domain entity following DDD principles with value objects, tests, and repository interface.

## Usage
`/ddd:entity [context] [entity-name]`

Example: `/ddd:entity Blog Article`

## Symfony Maker Integration

This command complements several Symfony Makers:

```bash
# Generate Infrastructure entity (Doctrine)
docker compose exec app bin/console make:infrastructure:entity [Context] [Entity]

# Generate Domain value objects
docker compose exec app bin:console make:domain:value-object [Context] [ValueObjectName]

# Examples:
docker compose exec app bin/console make:infrastructure:entity BlogContext Article
docker compose exec app bin/console make:domain:value-object BlogContext ArticleTitle
```

The Infrastructure Entity Maker will create:
- Doctrine entity with ORM mapping
- Repository implementation
- ID generator using UuidGenerator
- Migration file for database schema

The Value Object Maker will create:
- Value object with validation
- Using appropriate template (generic, email, URL, money, etc.)
- Following PHP 8.4 features

## Process

1. **Analyze Context**
   - Verify the context exists
   - Check existing domain structure
   - Identify related value objects

2. **Create Value Objects**
   - Generate ID value object using UuidGenerator
   - Create other value objects based on entity properties
   - Add validation in constructors
   - Use PHP 8.4 features (enums, readonly, asymmetric visibility)

3. **Create Repository Interface**
   ```php
   interface ArticleRepositoryInterface
   {
       public function save(Article $article): void;
       public function findById(ArticleId $id): ?Article;
       // Business-focused methods, not CRUD
   }
   ```

4. **Create Domain Entity**
   - Use value objects for properties
   - Implement business methods
   - Emit domain events when state changes
   - Follow aggregate design principles

5. **Create Infrastructure Entity**
   - Doctrine ORM mapping
   - Separate from domain entity
   - Map to database schema
   - Include indexes and constraints

6. **Generate Tests (TDD)**
   - PHPUnit test for domain entity
   - Test business invariants
   - Test value object validation
   - Mock repository for testing

7. **Create Migration**
   - Generate Doctrine migration
   - Review SQL before applying
   - Test rollback capability

## Quality Standards
- Follow @docs/reference/domain-layer-pattern.md
- Use @docs/reference/php-features-best-practices.md
- Apply TDD with PHPUnit for domain logic
- Run `composer qa` after implementation

## Template Integration
Use code snippets from `docs/agent/snippets/`:
- `domain/entity.md` for entity structure
- `domain/value-object.md` for value objects
- Repository interfaces follow standard patterns

## Next Steps
After creating entity:
1. Create aggregate if needed: `/ddd:aggregate`
2. Create commands/queries: `/spec:act`
3. Create API resource: `/api:resource`