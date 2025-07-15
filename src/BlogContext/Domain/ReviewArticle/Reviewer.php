<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle;

use App\BlogContext\Domain\ReviewArticle\DataPersister\ReviewedArticle;
use App\BlogContext\Domain\ReviewArticle\DataProvider\ArticleReview;
use App\BlogContext\Domain\ReviewArticle\Exception\InvalidReviewException;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;

final class Reviewer
{
    public function __invoke(ArticleReview $articleReview): ReviewedArticle
    {
        $this->validateCanBeReviewed($articleReview->status);

        if ($articleReview->decision->isApproved()) {
            return ReviewedArticle::approve(
                articleId: $articleReview->articleId,
                title: $articleReview->title,
                reviewerId: $articleReview->reviewerId,
                decision: $articleReview->decision,
                reviewedAt: $articleReview->reviewedAt,
            );
        }

        return ReviewedArticle::reject(
            articleId: $articleReview->articleId,
            title: $articleReview->title,
            reviewerId: $articleReview->reviewerId,
            decision: $articleReview->decision,
            reviewedAt: $articleReview->reviewedAt,
        );
    }

    private function validateCanBeReviewed(ArticleStatus $status): void
    {
        if ($status->isDraft()) {
            throw InvalidReviewException::invalidStatus($status);
        }

        if ($status->isApproved()) {
            throw InvalidReviewException::alreadyApproved();
        }

        if ($status->isPublished()) {
            throw InvalidReviewException::cannotReviewPublished();
        }

        if ($status->isArchived()) {
            throw InvalidReviewException::cannotReviewArchived();
        }

        if (!$status->canBeReviewed()) {
            throw InvalidReviewException::invalidStatus($status);
        }
    }
}
