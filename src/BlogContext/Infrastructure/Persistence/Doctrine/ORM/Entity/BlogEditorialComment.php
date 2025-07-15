<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_editorial_comments')]
#[ORM\Index(columns: ['article_id'], name: 'idx_editorial_comments_article_id')]
#[ORM\Index(columns: ['reviewer_id'], name: 'idx_editorial_comments_reviewer_id')]
#[ORM\Index(columns: ['created_at'], name: 'idx_editorial_comments_created_at')]
class BlogEditorialComment
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id,
        #[ORM\Column(type: UuidType::NAME)]
        private Uuid $articleId,
        #[ORM\Column(type: UuidType::NAME)]
        private Uuid $reviewerId,
        #[ORM\Column(type: Types::TEXT)]
        private string $comment,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private string|null $selectedText = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private int|null $positionStart = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private int|null $positionEnd = null,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getArticleId(): Uuid
    {
        return $this->articleId;
    }

    public function getReviewerId(): Uuid
    {
        return $this->reviewerId;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getSelectedText(): string|null
    {
        return $this->selectedText;
    }

    public function getPositionStart(): int|null
    {
        return $this->positionStart;
    }

    public function getPositionEnd(): int|null
    {
        return $this->positionEnd;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function setSelectedText(string|null $selectedText): void
    {
        $this->selectedText = $selectedText;
    }

    public function setPositionStart(int|null $positionStart): void
    {
        $this->positionStart = $positionStart;
    }

    public function setPositionEnd(int|null $positionEnd): void
    {
        $this->positionEnd = $positionEnd;
    }
}
