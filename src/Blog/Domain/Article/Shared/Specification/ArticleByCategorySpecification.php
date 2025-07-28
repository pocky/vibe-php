<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

final class ArticleByCategorySpecification extends CompositeSpecification
{
    public function __construct(
        private readonly CategoryId $categoryId,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        $articleCategoryId = $article->categoryId();

        return $articleCategoryId instanceof CategoryId && $articleCategoryId->equals($this->categoryId);
    }
}
