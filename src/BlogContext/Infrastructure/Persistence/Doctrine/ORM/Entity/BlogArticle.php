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
#[ORM\Index(columns: ['created_at'], name: 'idx_articles_created_at')]
class BlogArticle
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 200)]
        private string $title,
        #[ORM\Column(type: Types::TEXT)]
        private string $content,
        #[ORM\Column(type: Types::STRING, length: 250, unique: true)]
        private string $slug,
        #[ORM\Column(type: Types::STRING, length: 20)]
        private string $status,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $publishedAt = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $updatedAt = null
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPublishedAt(): \DateTimeImmutable|null
    {
        return $this->publishedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable|null
    {
        return $this->updatedAt;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setPublishedAt(\DateTimeImmutable|null $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable|null $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
