<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\Slug;

final class SlugAlreadyExists extends \DomainException
{
    public function __construct(Slug $slug)
    {
        parent::__construct(sprintf('Article with slug %s already exists', $slug->getValue()));
    }
}
