# Database Migration Workflow - From Domain to Database

## Overview

This workflow document illustrates the complete process for implementing database changes in our Domain-Driven Design architecture, from business requirements to database schema through Doctrine entities and migrations.

**Category**: Development Workflow  
**Related Instructions**: @docs/agent/instructions/doctrine-migrations.md  
**Reference Patterns**: @docs/reference/doctrine-orm.md

## Workflow Steps

### 1. Domain Model Definition (Business Requirements)

Start with the business requirement and define the domain model:

```php
// src/BlogContext/Domain/CreateArticle/DataPersister/Article.php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\DataPersister;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title, Content, Slug, ArticleStatus};

final class Article
{
    public function __construct(
        private readonly ArticleId $id,
        private readonly Title $title,
        private readonly Content $content,
        private readonly Slug $slug,
        private readonly ArticleStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $publishedAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    // Business logic methods...
    public function publish(): void
    {
        if ($this->status->isPublished()) {
            throw new ArticleAlreadyPublished($this->id);
        }
        
        $this->status = ArticleStatus::published();
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

### 2. Value Objects Definition

Define the value objects that encapsulate business rules:

```php
// src/BlogContext/Domain/Shared/ValueObject/Title.php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class Title
{
    public function __construct(
        private string $value
    ) {
        if (strlen(trim($value)) < 1) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        
        if (strlen($value) > 200) {
            throw new \InvalidArgumentException('Title cannot exceed 200 characters');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

```php
// src/BlogContext/Domain/Shared/ValueObject/ArticleStatus.php
<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final readonly class ArticleStatus
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const ARCHIVED = 'archived';

    private function __construct(
        private string $value
    ) {
        if (!in_array($value, [self::DRAFT, self::PUBLISHED, self::ARCHIVED], true)) {
            throw new \InvalidArgumentException('Invalid article status: ' . $value);
        }
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

### 3. Infrastructure Entity Creation

Create the Doctrine entity that maps to the database:

```php
// src/BlogContext/Infrastructure/Persistence/Doctrine/Entity/BlogArticle.php
<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_articles')]
#[ORM\Index(columns: ['status'], name: 'idx_articles_status')]
#[ORM\Index(columns: ['slug'], name: 'idx_articles_slug')]
#[ORM\Index(columns: ['published_at'], name: 'idx_articles_published_at')]
class BlogArticle
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 200)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::STRING, length: 250, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        Uuid $id,
        string $title,
        string $content,
        string $slug,
        string $status,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $publishedAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->slug = $slug;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->publishedAt = $publishedAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters and setters for ORM...
    public function getId(): Uuid { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    // ... other getters/setters
}
```

### 4. Generate Migration

Use Doctrine to generate the migration from the entity:

```bash
# Generate migration
docker compose exec app bin/console doctrine:migrations:diff
```

This creates a migration file like:

```php
// migrations/Version20250712120000.php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Create blog_articles table for Article aggregate
 * Iteration: 1 - Article Basics
 */
final class Version20250712120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create blog_articles table with indexes for basic article management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog_articles (
            id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
            title VARCHAR(200) NOT NULL,
            content LONGTEXT NOT NULL,
            slug VARCHAR(250) NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_IDENTIFIER_ID (id),
            UNIQUE INDEX UNIQ_989D9B62989D9B62 (slug),
            INDEX idx_articles_status (status),
            INDEX idx_articles_slug (slug),
            INDEX idx_articles_published_at (published_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE blog_articles');
    }
}
```

### 5. Review and Apply Migration

```bash
# Review the generated SQL (dry run)
docker compose exec app bin/console doctrine:migrations:migrate --dry-run

# Apply the migration
docker compose exec app bin/console doctrine:migrations:migrate

# Verify migration status
docker compose exec app bin/console doctrine:migrations:status
```

### 6. Repository Implementation

Create the repository that bridges domain and infrastructure:

```php
// src/BlogContext/Infrastructure/Persistence/Doctrine/Repository/ArticleRepository.php
<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\Repository;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Slug};
use App\BlogContext\Infrastructure\Persistence\Doctrine\Entity\BlogArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function save(Article $article): void
    {
        $entity = $this->mapToEntity($article);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    #[\Override]
    public function findById(ArticleId $id): ?Article
    {
        $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($id->toString()));
        
        return $entity ? $this->mapToDomain($entity) : null;
    }

    #[\Override]
    public function existsBySlug(Slug $slug): bool
    {
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(BlogArticle::class, 'a')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug->toString())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    private function mapToEntity(Article $article): BlogArticle
    {
        return new BlogArticle(
            id: Uuid::fromString($article->id()->toString()),
            title: $article->title()->toString(),
            content: $article->content()->toString(),
            slug: $article->slug()->toString(),
            status: $article->status()->toString(),
            createdAt: $article->createdAt(),
            publishedAt: $article->publishedAt(),
            updatedAt: $article->updatedAt()
        );
    }

    private function mapToDomain(BlogArticle $entity): Article
    {
        return new Article(
            id: new ArticleId($entity->getId()->toRfc4122()),
            title: new Title($entity->getTitle()),
            content: new Content($entity->getContent()),
            slug: new Slug($entity->getSlug()),
            status: ArticleStatus::fromString($entity->getStatus()),
            createdAt: $entity->getCreatedAt(),
            publishedAt: $entity->getPublishedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
    }
}
```

## Adding New Fields - Evolution Example

### Scenario: Adding Author Support (Iteration 2)

#### 1. Update Domain Model

```php
// Modify Article domain model
final class Article
{
    public function __construct(
        private readonly ArticleId $id,
        private readonly Title $title,
        private readonly Content $content,
        private readonly Slug $slug,
        private readonly ArticleStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private readonly ?AuthorId $authorId = null, // NEW
        private ?\DateTimeImmutable $publishedAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
```

#### 2. Create Author Value Object

```php
// src/BlogContext/Domain/Shared/ValueObject/AuthorId.php
final readonly class AuthorId
{
    public function __construct(
        private string $value
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid AuthorId UUID format');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

#### 3. Update Doctrine Entity

```php
// Add to BlogArticle entity
#[ORM\Column(type: UuidType::NAME, nullable: true)]
private ?Uuid $authorId = null;

public function getAuthorId(): ?Uuid { return $this->authorId; }
public function setAuthorId(?Uuid $authorId): void { $this->authorId = $authorId; }
```

#### 4. Generate Migration

```bash
docker compose exec app bin/console doctrine:migrations:diff
```

Generated migration:
```php
// migrations/Version20250712130000.php
final class Version20250712130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add author_id column to blog_articles table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_articles ADD author_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX idx_articles_author_id ON blog_articles (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_articles_author_id ON blog_articles');
        $this->addSql('ALTER TABLE blog_articles DROP author_id');
    }
}
```

#### 5. Update Repository Mapping

```php
// Update mapping methods in ArticleRepository
private function mapToEntity(Article $article): BlogArticle
{
    $entity = new BlogArticle(/* existing parameters */);
    
    if ($article->authorId()) {
        $entity->setAuthorId(Uuid::fromString($article->authorId()->toString()));
    }
    
    return $entity;
}

private function mapToDomain(BlogArticle $entity): Article
{
    $authorId = $entity->getAuthorId() ? 
        new AuthorId($entity->getAuthorId()->toRfc4122()) : 
        null;

    return new Article(
        // existing parameters
        authorId: $authorId,
    );
}
```

## Testing Strategy

### 1. Unit Tests for Domain

```php
// tests/BlogContext/Unit/Domain/CreateArticle/ArticleTest.php
final class ArticleTest extends TestCase
{
    public function testArticleCreationWithValidData(): void
    {
        $article = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            content: new Content('This is test content'),
            slug: new Slug('test-article'),
            status: ArticleStatus::draft(),
            createdAt: new \DateTimeImmutable(),
        );

        self::assertEquals('Test Article', $article->title()->toString());
        self::assertTrue($article->status()->isDraft());
    }
}
```

### 2. Integration Tests for Repository

```php
// tests/BlogContext/Integration/Infrastructure/Repository/ArticleRepositoryTest.php
final class ArticleRepositoryTest extends KernelTestCase
{
    private ArticleRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = new ArticleRepository($this->entityManager);
    }

    public function testSaveAndRetrieveArticle(): void
    {
        $article = new Article(/* test data */);
        
        $this->repository->save($article);
        
        $retrieved = $this->repository->findById($article->id());
        
        self::assertNotNull($retrieved);
        self::assertEquals($article->title()->toString(), $retrieved->title()->toString());
    }
}
```

### 3. Migration Tests

```bash
# Test migration in test environment
docker compose exec app_test bin/console doctrine:migrations:migrate --env=test

# Verify schema
docker compose exec app_test bin/console doctrine:schema:validate --env=test
```

## Quality Assurance Checklist

### Before Each Migration

- [ ] Domain model reflects business requirements
- [ ] Value objects enforce business rules
- [ ] Doctrine entity has proper mapping annotations
- [ ] Migration generates expected SQL
- [ ] Indexes are added for performance
- [ ] Repository mapping is bidirectional
- [ ] Unit tests cover domain logic
- [ ] Integration tests verify persistence
- [ ] All QA tools pass (`composer qa`)

### Verification Commands

```bash
# Verify schema consistency
docker compose exec app bin/console doctrine:schema:validate

# Check migration status
docker compose exec app bin/console doctrine:migrations:status

# Run all tests
docker compose exec app composer qa:tests

# Run all QA checks
docker compose exec app composer qa
```

## Common Patterns

### 1. Collection Relationships

```php
// Domain: Article with Tags
private array $tags = []; // Collection of TagId

// Entity: Use Doctrine collections
#[ORM\ManyToMany(targetEntity: BlogTag::class)]
#[ORM\JoinTable(name: 'blog_article_tags')]
private Collection $tags;
```

### 2. Enum-like Value Objects

```php
// Domain: Status with business rules
final readonly class ArticleStatus
{
    // Business logic for status transitions
    public function canTransitionTo(self $newStatus): bool
    {
        return match ([$this->value, $newStatus->value]) {
            ['draft', 'published'] => true,
            ['published', 'archived'] => true,
            default => false,
        };
    }
}

// Entity: Simple string column
#[ORM\Column(type: Types::STRING, length: 20)]
private string $status;
```

### 3. Soft Deletes

```php
// Domain: Deletion business logic
public function archive(): void
{
    if ($this->status->isArchived()) {
        throw new ArticleAlreadyArchived($this->id);
    }
    $this->status = ArticleStatus::archived();
}

// Entity: Add deleted_at column
#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
private ?\DateTimeImmutable $archivedAt = null;
```

## Conclusion

This workflow ensures:
- **Business rules drive technical decisions**
- **Database schema evolves with domain model**
- **Migrations are generated, not hand-written**
- **Changes are traceable and reversible**
- **Quality is maintained through testing**

The key is maintaining the separation between domain logic (business rules) and infrastructure concerns (persistence) while ensuring they evolve together in a controlled, testable manner.