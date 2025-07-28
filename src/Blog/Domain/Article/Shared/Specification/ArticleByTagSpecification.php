<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;

final class ArticleByTagSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly TagId $tagId,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        foreach ($article->tagIds() as $articleTagId) {
            if ($articleTagId->equals($this->tagId)) {
                return true;
            }
        }

        return false;
    }
}
