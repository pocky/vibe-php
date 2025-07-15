<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\DataPersister;

use App\BlogContext\Domain\ReviewArticle\Event\ArticleApproved;
use App\BlogContext\Domain\ReviewArticle\Event\ArticleRejected;
use App\BlogContext\Domain\Shared\Event\EventRecorder;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final class ReviewedArticle
{
    use EventRecorder;

    public function __construct(
        private readonly ArticleId $articleId,
        private readonly Title $title,
        private readonly ArticleStatus $status,
        private readonly string $reviewerId,
        private readonly ReviewDecision $decision,
        private readonly \DateTimeImmutable $reviewedAt,
    ) {
    }

    public static function approve(
        ArticleId $articleId,
        Title $title,
        string $reviewerId,
        ReviewDecision $decision,
        \DateTimeImmutable $reviewedAt,
    ): self {
        $article = new self(
            $articleId,
            $title,
            ArticleStatus::APPROVED,
            $reviewerId,
            $decision,
            $reviewedAt
        );

        $article->recordEvent(new ArticleApproved(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            reviewerId: $reviewerId,
            approvalReason: $decision->getReason(),
            reviewedAt: $reviewedAt,
        ));

        return $article;
    }

    public static function reject(
        ArticleId $articleId,
        Title $title,
        string $reviewerId,
        ReviewDecision $decision,
        \DateTimeImmutable $reviewedAt,
    ): self {
        $article = new self(
            $articleId,
            $title,
            ArticleStatus::REJECTED,
            $reviewerId,
            $decision,
            $reviewedAt
        );

        $article->recordEvent(new ArticleRejected(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            reviewerId: $reviewerId,
            rejectionReason: $decision->getReason() ?? '',
            reviewedAt: $reviewedAt,
        ));

        return $article;
    }

    public function getArticleId(): ArticleId
    {
        return $this->articleId;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getReviewerId(): string
    {
        return $this->reviewerId;
    }

    public function getDecision(): ReviewDecision
    {
        return $this->decision;
    }

    public function getReviewedAt(): \DateTimeImmutable
    {
        return $this->reviewedAt;
    }
}
