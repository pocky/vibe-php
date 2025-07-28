<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;

final class PublishedArticleSpecification extends CompositeSpecification
{
    public function isSatisfiedBy(Article $article): bool
    {
        return ArticleStatus::PUBLISHED === $article->status();
    }
}
