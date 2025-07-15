<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\DataProvider;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final readonly class ArticleReview
{
    public function __construct(
        public ArticleId $articleId,
        public Title $title,
        public ArticleStatus $status,
        public string $reviewerId,
        public ReviewDecision $decision,
        public \DateTimeImmutable $reviewedAt,
    ) {
    }
}
