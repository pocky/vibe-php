# Doctrine Entity Snippets

## Modern Doctrine Entity Pattern

Following the pattern used in Article and Author entities, all Doctrine entities should use public properties with constructor property promotion.

### Basic Entity Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: '[context]_[entities]')]
#[ORM\Index(columns: ['name'], name: 'idx_[entities]_name')]
#[ORM\Index(columns: ['slug'], name: 'idx_[entities]_slug')]
#[ORM\UniqueConstraint(name: 'uniq_[entities]_slug', columns: ['slug'])]
class [Entity]
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        
        #[ORM\Column(type: Types::STRING, length: 200)]
        public string $name,
        
        #[ORM\Column(type: Types::STRING, length: 250, unique: true)]
        public string $slug,
        
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        public string|null $description,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt
    ) {
    }
}
```

### Entity with Relations

```php
#[ORM\Entity]
#[ORM\Table(name: '[context]_[entities]')]
#[ORM\Index(columns: ['parent_id'], name: 'idx_[entities]_parent_id')]
class [Entity]
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        
        #[ORM\Column(type: Types::STRING, length: 200)]
        public string $name,
        
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        public Uuid|null $parentId,
        
        #[ORM\Column(type: Types::INTEGER)]
        public int $order,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt
    ) {
    }
}
```

### Repository Mapping Methods

When working with these entities in repositories, access properties directly:

```php
private function mapToEntity(Domain[Entity] $domain[Entity]): Doctrine[Entity]
{
    return new Doctrine[Entity](
        id: Uuid::fromString($domain[Entity]->id()->getValue()),
        name: $domain[Entity]->name()->getValue(),
        slug: $domain[Entity]->slug()->getValue(),
        description: $domain[Entity]->description()?->getValue(),
        createdAt: $domain[Entity]->createdAt(),
        updatedAt: $domain[Entity]->updatedAt()
    );
}

private function mapToDomain(Doctrine[Entity] $entity): Domain[Entity]
{
    return new Domain[Entity](
        id: new [Entity]Id($entity->id->toRfc4122()),
        name: new [Entity]Name($entity->name),
        slug: new [Entity]Slug($entity->slug),
        description: $entity->description ? new Description($entity->description) : null,
        createdAt: $entity->createdAt,
        updatedAt: $entity->updatedAt
    );
}

// Updating existing entity
if ($existingEntity) {
    $existingEntity->name = $domain[Entity]->name()->getValue();
    $existingEntity->slug = $domain[Entity]->slug()->getValue();
    $existingEntity->updatedAt = new \DateTimeImmutable();
    
    return $existingEntity;
}
```

## Key Points

1. **Public Properties**: All properties are public for direct access
2. **Constructor Property Promotion**: Use PHP 8.4 constructor property promotion
3. **UUID as Primary Key**: Always use Symfony\Component\Uid\Uuid for IDs
4. **No Getters/Setters**: Direct property access eliminates need for getters/setters
5. **Type Declarations**: Use proper type hints including union types for nullable
6. **DateTimeImmutable**: Always use immutable date objects
7. **Table Naming**: Use `[context]_[entities]` pattern (e.g., `blog_articles`)
8. **Index Naming**: Use descriptive index names with `idx_` or `uniq_` prefix