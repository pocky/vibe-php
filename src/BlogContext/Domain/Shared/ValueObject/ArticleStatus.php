<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PENDING_REVIEW = 'pending_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public static function fromString(string $status): self
    {
        return self::from($status);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this;
    }

    public function isArchived(): bool
    {
        return self::ARCHIVED === $this;
    }

    public function isPendingReview(): bool
    {
        return self::PENDING_REVIEW === $this;
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this;
    }

    public function isRejected(): bool
    {
        return self::REJECTED === $this;
    }

    public function canBeSubmittedForReview(): bool
    {
        return match ($this) {
            self::DRAFT, self::REJECTED => true,
            default => false,
        };
    }

    public function canBeReviewed(): bool
    {
        return self::PENDING_REVIEW === $this;
    }

    public function canBePublished(): bool
    {
        return self::APPROVED === $this;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
