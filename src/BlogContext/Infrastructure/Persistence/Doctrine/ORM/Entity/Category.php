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
#[ORM\Index(columns: ['parent_id'], name: 'idx_categories_parent_id')]
#[ORM\UniqueConstraint(name: 'uniq_categories_slug', columns: ['slug'])]
#[ORM\UniqueConstraint(name: 'uniq_categories_name', columns: ['name'])]
class Category
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 100)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 120)]
        private string $slug,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private string|null $description,
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        private Uuid|null $parentId,
        #[ORM\Column(type: Types::INTEGER)]
        private int $order,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $updatedAt
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function setDescription(string|null $description): void
    {
        $this->description = $description;
    }

    public function getParentId(): Uuid|null
    {
        return $this->parentId;
    }

    public function setParentId(Uuid|null $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
