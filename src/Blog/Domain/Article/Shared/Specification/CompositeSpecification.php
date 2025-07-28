<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;

abstract class CompositeSpecification implements ArticleSpecification
{
    public function and(ArticleSpecification $articleSpecification): ArticleSpecification
    {
        return new AndSpecification($this, $articleSpecification);
    }

    public function or(ArticleSpecification $articleSpecification): ArticleSpecification
    {
        return new OrSpecification($this, $articleSpecification);
    }

    public function not(): ArticleSpecification
    {
        return new NotSpecification($this);
    }
}

final class AndSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly ArticleSpecification $left,
        private readonly ArticleSpecification $right,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        return $this->left->isSatisfiedBy($article) && $this->right->isSatisfiedBy($article);
    }
}

final class OrSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly ArticleSpecification $left,
        private readonly ArticleSpecification $right,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        if ($this->left->isSatisfiedBy($article)) {
            return true;
        }

        return $this->right->isSatisfiedBy($article);
    }
}

final class NotSpecification extends CompositeSpecification
{
    public function __construct(
        private readonly ArticleSpecification $articleSpecification,
    ) {
    }

    public function isSatisfiedBy(Article $article): bool
    {
        return !$this->articleSpecification->isSatisfiedBy($article);
    }
}
