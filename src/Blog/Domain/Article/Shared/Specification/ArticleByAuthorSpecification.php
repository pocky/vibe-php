<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

final class ArticleByAuthorSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly AuthorId $authorId,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        return $article->authorId()->equals($this->authorId);
    }
}
