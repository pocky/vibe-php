---
description: Create and manage Doctrine migrations
allowed-tools: Read(*), Write(*), Edit(*), MultiEdit(*), Glob(*), Grep(*), Bash(*), TodoWrite
---

# Doctrine Migration Management

Create database migrations following Doctrine best practices and DDD principles.

## Usage
`/ddd:migration [context] [description]`

Example: `/ddd:migration Blog add-category-to-articles`

## Process

1. **Analyze Entity Changes**
   - Check modified Doctrine entities
   - Identify new properties/relations
   - Review index requirements
   - Validate column types

2. **Generate Migration**
   ```bash
   docker compose exec app bin/console doctrine:migrations:diff
   ```

3. **Review Generated SQL**
   - Check CREATE TABLE statements
   - Verify ALTER TABLE commands
   - Ensure indexes are created
   - Validate foreign key constraints

4. **Customize Migration if Needed**
   ```php
   public function up(Schema $schema): void
   {
       // Generated SQL
       $this->addSql('ALTER TABLE blog_articles ADD category_id CHAR(36) DEFAULT NULL');
       $this->addSql('CREATE INDEX IDX_CATEGORY ON blog_articles (category_id)');
       
       // Custom data migration if needed
       $this->addSql('UPDATE blog_articles SET category_id = ? WHERE status = ?', 
           ['default-category-id', 'published']
       );
   }
   
   public function down(Schema $schema): void
   {
       // Rollback SQL
       $this->addSql('DROP INDEX IDX_CATEGORY ON blog_articles');
       $this->addSql('ALTER TABLE blog_articles DROP category_id');
   }
   ```

5. **Test Migration**
   ```bash
   # Dry run first
   docker compose exec app bin/console doctrine:migrations:migrate --dry-run
   
   # Apply migration
   docker compose exec app bin/console doctrine:migrations:migrate
   
   # Test rollback (dev only)
   docker compose exec app bin/console doctrine:migrations:execute --down Version20250119120000
   ```

6. **Update Tests**
   - Run test suite with new schema
   - Update fixtures if needed
   - Verify entity mappings work

7. **Document Changes**
   - Add migration notes
   - Update entity documentation
   - Note any breaking changes

## Best Practices

### Entity-First Approach
- Always modify entities first
- Let Doctrine generate migrations
- Never write SQL manually unless necessary

### Performance Considerations
- Add indexes for foreign keys
- Consider column order for composite indexes
- Use appropriate column types
- Plan for large table migrations

### Safety Measures
- Always backup before production migrations
- Test rollback capability
- Consider maintenance windows for large changes
- Use transactions when possible

## Common Patterns

### Adding New Column
```php
#[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
private ?string $newField = null;
```

### Adding Relation
```php
#[ORM\ManyToOne(targetEntity: Category::class)]
#[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
private ?Category $category = null;
```

### Adding Index
```php
#[ORM\Table(name: 'blog_articles')]
#[ORM\Index(columns: ['status', 'published_at'], name: 'idx_status_date')]
class BlogArticle
```

## Troubleshooting
- Schema out of sync: Run `doctrine:schema:validate`
- Migration exists: Check with `doctrine:migrations:status`
- Foreign key fails: Ensure referenced table exists

## Quality Standards
- Follow @docs/agent/instructions/doctrine-migrations.md
- Test migrations in development first
- Document breaking changes
- Commit migrations with related code

## Next Steps
1. Update repository if needed
2. Run QA tools: `composer qa`
3. Update API if schema changed
4. Create fixtures for new data