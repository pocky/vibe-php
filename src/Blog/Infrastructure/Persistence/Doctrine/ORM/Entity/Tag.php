<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_tags')]
#[ORM\UniqueConstraint(name: 'UNIQ_BLOG_TAG_SLUG', fields: ['slug'])]
#[ORM\Index(name: 'IDX_BLOG_TAG_NAME', fields: ['name'])]
#[ORM\Index(name: 'IDX_BLOG_TAG_ARTICLE_COUNT', fields: ['articleCount'])]
class Tag
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 100)]
        public string $name,
        #[ORM\Column(type: Types::STRING, length: 100)]
        public string $slug,
        #[ORM\Column(type: Types::INTEGER, options: [
            'default' => 0,
        ])]
        public int $articleCount,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
