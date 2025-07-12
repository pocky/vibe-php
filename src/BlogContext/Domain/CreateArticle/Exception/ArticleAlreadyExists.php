<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\Slug;

final class ArticleAlreadyExists extends \DomainException
{
    public function __construct(
        private readonly Slug $slug,
    ) {
        parent::__construct(
            sprintf('Article with slug "%s" already exists', $slug->getValue())
        );
    }

    public function slug(): Slug
    {
        return $this->slug;
    }
}
