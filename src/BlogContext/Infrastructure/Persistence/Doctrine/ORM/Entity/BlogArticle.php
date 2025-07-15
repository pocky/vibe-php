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
#[ORM\Index(columns: ['submitted_at'], name: 'idx_articles_submitted_at')]
#[ORM\Index(columns: ['reviewed_at'], name: 'idx_articles_reviewed_at')]
#[ORM\Index(columns: ['reviewer_id'], name: 'idx_articles_reviewer_id')]
#[ORM\Index(columns: ['status', 'submitted_at'], name: 'idx_articles_review_queue')]
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
        private \DateTimeImmutable|null $updatedAt = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $publishedAt = null,
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        private Uuid|null $authorId = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $submittedAt = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private \DateTimeImmutable|null $reviewedAt = null,
        #[ORM\Column(type: UuidType::NAME, nullable: true)]
        private Uuid|null $reviewerId = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private string|null $approvalReason = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private string|null $rejectionReason = null
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

    public function getUpdatedAt(): \DateTimeImmutable|null
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): \DateTimeImmutable|null
    {
        return $this->publishedAt;
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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getAuthorId(): Uuid|null
    {
        return $this->authorId;
    }

    public function setAuthorId(Uuid|null $authorId): void
    {
        $this->authorId = $authorId;
    }

    public function getSubmittedAt(): \DateTimeImmutable|null
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable|null $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }

    public function getReviewedAt(): \DateTimeImmutable|null
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(\DateTimeImmutable|null $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }

    public function getReviewerId(): Uuid|null
    {
        return $this->reviewerId;
    }

    public function setReviewerId(Uuid|null $reviewerId): void
    {
        $this->reviewerId = $reviewerId;
    }

    public function getApprovalReason(): string|null
    {
        return $this->approvalReason;
    }

    public function setApprovalReason(string|null $approvalReason): void
    {
        $this->approvalReason = $approvalReason;
    }

    public function getRejectionReason(): string|null
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(string|null $rejectionReason): void
    {
        $this->rejectionReason = $rejectionReason;
    }

    public function updateContent(string $title, string $content, string $slug): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
}
