# Blog Context Implementation Guide

## Overview

This guide provides step-by-step instructions for implementing the Blog context based on the technical design. Follow the implementation phases in order to ensure proper dependencies and system integrity.

## Prerequisites

- PHP 8.4+ environment
- Symfony 7.3 framework installed
- Docker environment running
- Database (MySQL/PostgreSQL) configured
- Composer dependencies installed

## Implementation Phases

### Phase 1: Foundation (US-001) - Basic Article Management

**Objective**: Establish core article functionality as the foundation for all other features.

#### Step 1: Domain Layer
1. Create value objects:
   ```
   src/BlogContext/Domain/Shared/ValueObject/
   ├── ArticleId.php
   ├── Title.php
   ├── Content.php
   ├── Slug.php
   └── Timestamps.php
   ```

2. Create ArticleStatus enum:
   ```
   src/BlogContext/Domain/Shared/ValueObject/ArticleStatus.php
   ```

3. Create Article aggregate:
   ```
   src/BlogContext/Domain/Article/Article.php
   ```

4. Define domain events:
   ```
   src/BlogContext/Domain/Article/Event/
   ├── ArticleCreated.php
   ├── ArticleUpdated.php
   ├── ArticlePublished.php
   └── ArticleDeleted.php
   ```

#### Step 2: Application Layer
1. Create commands:
   ```
   src/BlogContext/Application/Operation/Command/
   ├── CreateArticle/
   │   ├── Command.php
   │   └── Handler.php
   ├── UpdateArticle/
   │   ├── Command.php
   │   └── Handler.php
   ├── PublishArticle/
   │   ├── Command.php
   │   └── Handler.php
   └── DeleteArticle/
       ├── Command.php
       └── Handler.php
   ```

2. Create queries:
   ```
   src/BlogContext/Application/Operation/Query/
   ├── GetArticle/
   │   ├── Query.php
   │   ├── Handler.php
   │   └── View.php
   └── ListArticles/
       ├── Query.php
       ├── Handler.php
       └── View.php
   ```

3. Create gateways:
   ```
   src/BlogContext/Application/Gateway/
   ├── CreateArticle/
   │   ├── Gateway.php
   │   ├── Request.php
   │   ├── Response.php
   │   └── Middleware/
   │       ├── Validation.php
   │       └── Processor.php
   └── [Similar structure for other operations]
   ```

#### Step 3: Infrastructure Layer
1. Create repository interface:
   ```
   src/BlogContext/Domain/Shared/Repository/ArticleRepositoryInterface.php
   ```

2. Create Doctrine entity:
   ```
   src/BlogContext/Infrastructure/Persistence/Doctrine/Entity/BlogArticle.php
   ```

3. Implement repository:
   ```
   src/BlogContext/Infrastructure/Persistence/Doctrine/Repository/ArticleRepository.php
   ```

4. Create database migration:
   ```bash
   docker compose exec app bin/console doctrine:migrations:diff
   # Review and execute migration
   docker compose exec app bin/console doctrine:migrations:migrate
   ```

#### Step 4: Testing
1. Write unit tests for domain objects
2. Write integration tests for repositories
3. Write functional tests for gateways

### Phase 2: Categories & Authors (US-002, US-003)

**Objective**: Add categorization and authorship capabilities.

#### Category Implementation (US-002)
1. Create Category domain model
2. Implement category commands and queries
3. Create category gateways
4. Add blog_categories table
5. Create blog_article_categories junction table
6. Update Article to support categories

#### Author Implementation (US-003)
1. Create Author domain model
2. Implement author commands and queries
3. Create author gateways
4. Add blog_authors table
5. Update Article to require author

### Phase 3: Tags & API (US-004, US-008)

**Objective**: Complete content organization and expose API endpoints.

#### Tag Implementation (US-004)
1. Create Tag value object
2. Add tag handling to Article commands
3. Create blog_tags table
4. Create blog_article_tags junction table

#### API Implementation (US-008)
1. Configure API Platform
2. Create API resources:
   ```
   src/BlogContext/UI/Api/Rest/Resource/
   ├── ArticleResource.php
   ├── CategoryResource.php
   └── AuthorResource.php
   ```

3. Implement state providers and processors
4. Configure serialization groups
5. Set up CORS and rate limiting

### Phase 4: Admin UI (US-005, US-006, US-007)

**Objective**: Create administrative interfaces for content management.

1. Set up Sylius Admin UI components
2. Create admin controllers
3. Implement CRUD interfaces
4. Add bulk operations
5. Integrate with existing gateway layer

## Development Workflow

### For Each Component:
1. **Write failing test first** (TDD approach)
2. **Implement minimal code** to make test pass
3. **Refactor** while keeping tests green
4. **Run QA tools** after each component:
   ```bash
   docker compose exec app composer qa
   ```

### Git Workflow:
```bash
# Feature branch
git checkout -b feature/blog-article-management

# After implementing and testing
git add .
git commit -m "feat(blog): implement Article aggregate and value objects"

# Continue with small, focused commits
```

### Quality Checklist:
- [ ] All tests pass
- [ ] PHPStan analysis passes
- [ ] ECS coding standards met
- [ ] Documentation updated
- [ ] No TODO comments left

## Common Implementation Patterns

### Value Object Pattern:
```php
final class Title
{
    public function __construct(
        private(set) string $value
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (mb_strlen($this->value) < 1 || mb_strlen($this->value) > 200) {
            throw new \InvalidArgumentException('Title must be between 1 and 200 characters');
        }
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
}
```

### Command Handler Pattern:
```php
final readonly class CreateArticleHandler
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
        private EventBusInterface $eventBus,
        private SlugGeneratorInterface $slugGenerator,
    ) {}
    
    public function __invoke(CreateArticleCommand $command): void
    {
        $article = Article::create(
            ArticleId::generate(),
            Title::fromString($command->title),
            Content::fromString($command->content),
            $this->slugGenerator->generate($command->title),
            AuthorId::fromString($command->authorId)
        );
        
        $this->repository->save($article);
        $this->eventBus->dispatch(...$article->releaseEvents());
    }
}
```

### Gateway Pattern:
```php
final class CreateArticleGateway extends DefaultGateway
{
    public function __construct(
        private readonly CreateArticleHandler $handler,
        private readonly DefaultLogger $logger,
        private readonly DefaultErrorHandler $errorHandler,
    ) {
        parent::__construct(
            $this->logger,
            $this->errorHandler,
            new CreateArticleValidation(),
            new CreateArticleProcessor($this->handler)
        );
    }
}
```

## Troubleshooting

### Common Issues:

1. **Namespace not found**:
   - Check PSR-4 compliance
   - Run `composer dump-autoload`

2. **Database migration fails**:
   - Check foreign key constraints
   - Ensure proper order of migrations

3. **Tests fail**:
   - Check test database is migrated
   - Clear test cache

4. **QA tools fail**:
   - Run individual tools to identify issue
   - Fix one tool at a time

## Performance Optimization

1. **Database indexes**: Already defined in migrations
2. **Query optimization**: Use joins for related data
3. **Caching**: Implement after Phase 3
4. **Pagination**: Always paginate list operations

## Security Considerations

1. **Input validation**: At gateway and domain levels
2. **XSS prevention**: Sanitize content on input
3. **SQL injection**: Use parameterized queries
4. **Rate limiting**: Configure in API phase

## Next Steps

After completing all phases:
1. Performance testing
2. Security audit
3. Documentation review
4. Deployment preparation

Remember to follow TDD principles and run QA tools continuously throughout implementation.