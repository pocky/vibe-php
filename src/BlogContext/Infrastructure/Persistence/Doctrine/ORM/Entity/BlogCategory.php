<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_categories')]
#[ORM\Index(columns: ['slug'], name: 'idx_categories_slug')]
#[ORM\Index(columns: ['path'], name: 'idx_categories_path')]
#[ORM\Index(columns: ['parent_id'], name: 'idx_categories_parent_id')]
#[ORM\Index(columns: ['level'], name: 'idx_categories_level')]
class BlogCategory
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 100)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 120, unique: true)]
        private string $slug,
        #[ORM\Column(type: Types::STRING, length: 500)]
        private string $path,
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        private Uuid|null $parentId = null,
        #[ORM\Column(type: Types::SMALLINT)]
        private int $level = 1,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private string|null $description = null,
        #[ORM\Column(type: Types::INTEGER)]
        private int $articleCount = 0,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $updatedAt = null,
    ) {
    }

    // Getters
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParentId(): Uuid|null
    {
        return $this->parentId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getArticleCount(): int
    {
        return $this->articleCount;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable|null
    {
        return $this->updatedAt;
    }

    // Setters for updates
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setParentId(Uuid|null $parentId): void
    {
        $this->parentId = $parentId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setDescription(string|null $description): void
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function incrementArticleCount(): void
    {
        ++$this->articleCount;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function decrementArticleCount(): void
    {
        $this->articleCount = max(0, $this->articleCount - 1);
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Business methods
    public function isRoot(): bool
    {
        return !$this->parentId instanceof Uuid;
    }

    public function hasParent(): bool
    {
        return $this->parentId instanceof Uuid;
    }

    public function isChild(): bool
    {
        return !$this->isRoot();
    }
}
