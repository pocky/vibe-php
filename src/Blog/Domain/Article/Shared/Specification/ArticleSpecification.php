<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Specification;

use App\Blog\Domain\Article\Shared\Model\Article;

interface ArticleSpecification
{
    public function isSatisfiedBy(Article $article): bool;

    public function and(self $specification): self;

    public function or(self $specification): self;

    public function not(): self;
}
