<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity;

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
        public Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 100)]
        public string $name,
        #[ORM\Column(type: Types::STRING, length: 120)]
        public string $slug,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        public string|null $description,
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        public Uuid|null $parentId,
        #[ORM\Column(type: Types::INTEGER)]
        public int $order,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
