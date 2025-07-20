<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_articles')]
#[ORM\Index(columns: ['status'], name: 'idx_articles_status')]
#[ORM\Index(columns: ['slug'], name: 'idx_articles_slug')]
#[ORM\Index(columns: ['published_at'], name: 'idx_articles_published_at')]
#[ORM\Index(columns: ['author_id'], name: 'idx_articles_author_id')]
class Article
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 200)]
        public string $title,
        #[ORM\Column(type: Types::TEXT)]
        public string $content,
        #[ORM\Column(type: Types::STRING, length: 250, unique: true)]
        public string $slug,
        #[ORM\Column(type: Types::STRING, length: 20)]
        public string $status,
        #[ORM\Column(type: Types::STRING, length: 36)]
        public string $authorId,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        public \DateTimeImmutable|null $publishedAt = null
    ) {
    }
}
