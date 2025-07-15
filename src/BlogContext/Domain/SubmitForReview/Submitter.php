<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\SubmitForReview;

use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\SubmitForReview\DataPersister\Article;
use App\BlogContext\Domain\SubmitForReview\DataProvider\ArticleForReview;
use App\BlogContext\Domain\SubmitForReview\Exception\InvalidSubmissionException;

final class Submitter
{
    public function __invoke(ArticleForReview $articleForReview): Article
    {
        $this->validateCanBeSubmitted($articleForReview->status);

        return Article::submitForReview(
            articleId: $articleForReview->articleId,
            title: $articleForReview->title,
            status: ArticleStatus::PENDING_REVIEW,
            submittedAt: $articleForReview->submittedAt,
        );
    }

    private function validateCanBeSubmitted(ArticleStatus $status): void
    {
        if ($status->isPendingReview()) {
            throw InvalidSubmissionException::alreadyPendingReview();
        }

        if ($status->isApproved()) {
            throw InvalidSubmissionException::alreadyApproved();
        }

        if ($status->isPublished()) {
            throw InvalidSubmissionException::cannotSubmitPublished();
        }

        if ($status->isArchived()) {
            throw InvalidSubmissionException::cannotSubmitArchived();
        }
    }
}
