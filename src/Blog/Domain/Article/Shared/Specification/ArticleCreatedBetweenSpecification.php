<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;

final class ArticleCreatedBetweenSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly \DateTimeImmutable $from,
        private readonly \DateTimeImmutable $to,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        $createdAt = $article->createdAt();

        return $createdAt >= $this->from && $createdAt <= $this->to;
    }
}
