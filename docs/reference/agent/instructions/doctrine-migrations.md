# Doctrine Migrations

**Entity-first approach for DDD database management.**

## Principles

1. **Entity-First**: Never write SQL, use entities
2. **Generate**: `doctrine:migrations:diff`
3. **Review**: Check SQL before applying
4. **Workflow**: Entity → Generate → Review → Apply → Test → Commit

## Structure
```
migrations/Version*.php
src/[Context]/Infrastructure/Persistence/Doctrine/Entity/
```

## Commands
```bash
# Status/Generate/Apply
doctrine:migrations:status
doctrine:migrations:diff
doctrine:migrations:migrate

# Debug
doctrine:migrations:migrate --dry-run
doctrine:schema:validate

# Rollback (caution)
doctrine:migrations:execute --down Version*
```

## Entity Standards

```php
#[ORM\Entity]
#[ORM\Table(name: 'context_entities')]
class Entity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;
    
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;
}
```

**Rules**:
- UUID primary keys
- Table: `{context}_{entity}`
- DATETIME_IMMUTABLE for dates
- Indexes on query columns
- Constructor initialization

## Migration Patterns

### New Table
1. Create entity
2. `doctrine:migrations:diff`
3. Review CREATE TABLE

### Add Column
```php
#[ORM\Column(type: UuidType::NAME, nullable: true)]
private ?Uuid $authorId = null;
```

### Add Relation
```php
#[ORM\ManyToOne(targetEntity: Author::class)]
#[ORM\JoinColumn(name: 'author_id')]
```

### Add Index
```php
#[ORM\Index(columns: ['author_id'])]
```

## Workflow Integration

### TDD
1. Write test
2. Create/modify entity
3. Generate & apply migration
4. Run tests

### PR Checklist
```bash
doctrine:migrations:migrate
doctrine:schema:validate
composer qa
```

### Git
Commit together:
- Migration files
- Entity changes
- Domain models
- Tests

## Environments

**Dev**: Reset DB + migrate + fixtures
**Test**: Create + migrate --env=test
**Prod**: Backup + dry-run + migrate

## Troubleshooting

**"Migration already exists"**: Check status, mark as executed
**"Schema not in sync"**: Validate, generate diff
**"FK constraint fails"**: Check relationships, types

**Rollback**: Prefer new migration over --down

## Quality Checklist
☑ Expected SQL
☑ No data loss
☑ Proper indexes
☑ FK constraints
☑ Column types
☑ Reversible

**Performance**:
- Index: FKs, search, sort columns
- Types: STRING(length), TEXT, UUID, DATETIME_IMMUTABLE
- Large tables: nullable first, populate, then NOT NULL

## DDD Integration

**Domain vs Entity**: Separate models
**Repository**: Maps domain ↔ infrastructure

See @docs/archive/implementation-summaries/doctrine-orm.md

## Future Considerations

**Evolution**: Additive → Modify → Remove (with deprecation)
**Cross-Context**: Reference IDs only, no FKs

## Related Docs
- @docs/development/workflows/database-migration-workflow.md
- @docs/archive/implementation-summaries/doctrine-orm.md
