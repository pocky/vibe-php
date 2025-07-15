<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture\Factory;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<BlogArticle>
 */
final class BlogArticleFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return BlogArticle::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        $status = self::faker()->randomElement(['draft', 'published']);

        return [
            'id' => Uuid::v7(),
            'title' => self::faker()->sentence(4),
            'content' => self::faker()->paragraphs(3, true),
            'slug' => self::faker()->slug(),
            'status' => $status,
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => null,
            'publishedAt' => 'published' === $status ? new \DateTimeImmutable() : null,
            'authorId' => Uuid::v7(),
            'submittedAt' => null,
            'reviewedAt' => null,
            'reviewerId' => null,
            'approvalReason' => null,
            'rejectionReason' => null,
        ];
    }

    public function draft(): static
    {
        return $this->with([
            'status' => 'draft',
            'publishedAt' => null,
        ]);
    }

    public function published(): static
    {
        return $this->with([
            'status' => 'published',
            'publishedAt' => new \DateTimeImmutable(),
        ]);
    }

    public function withTitle(string $title): static
    {
        return $this->with([
            'title' => $title,
            'slug' => self::faker()->slug(),
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->with([
            'slug' => $slug,
        ]);
    }

    public function pendingReview(): static
    {
        return $this->with([
            'status' => 'pending_review',
            'submittedAt' => new \DateTimeImmutable(),
            'publishedAt' => null,
            'reviewedAt' => null,
            'reviewerId' => null,
            'approvalReason' => null,
            'rejectionReason' => null,
        ]);
    }

    public function approved(): static
    {
        $reviewedAt = new \DateTimeImmutable();

        return $this->with([
            'status' => 'approved',
            'submittedAt' => new \DateTimeImmutable('-2 days'),
            'reviewedAt' => $reviewedAt,
            'reviewerId' => Uuid::v7(),
            'approvalReason' => self::faker()->sentence(8),
            'rejectionReason' => null,
            'publishedAt' => null,
        ]);
    }

    public function rejected(): static
    {
        $reviewedAt = new \DateTimeImmutable();

        return $this->with([
            'status' => 'rejected',
            'submittedAt' => new \DateTimeImmutable('-3 days'),
            'reviewedAt' => $reviewedAt,
            'reviewerId' => Uuid::v7(),
            'approvalReason' => null,
            'rejectionReason' => self::faker()->paragraphs(2, true),
            'publishedAt' => null,
        ]);
    }

    public function withAuthor(string $authorId): static
    {
        return $this->with([
            'authorId' => Uuid::fromString($authorId),
        ]);
    }

    public function withReviewer(string $reviewerId): static
    {
        return $this->with([
            'reviewerId' => Uuid::fromString($reviewerId),
        ]);
    }
}
