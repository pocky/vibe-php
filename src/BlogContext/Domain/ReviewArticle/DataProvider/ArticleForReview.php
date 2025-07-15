<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\DataProvider;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final readonly class ArticleForReview
{
    public function __construct(
        public ArticleId $articleId,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public string $reviewerId,
        public \DateTimeImmutable $reviewedAt,
        public ReviewDecision $decision,
    ) {
    }
}
