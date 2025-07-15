<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\SubmitForReview\DataProvider;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final readonly class ArticleForReview
{
    public function __construct(
        public ArticleId $articleId,
        public Title $title,
        public ArticleStatus $status,
        public \DateTimeImmutable $submittedAt,
    ) {
    }
}
