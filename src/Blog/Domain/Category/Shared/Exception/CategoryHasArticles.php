<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Exception;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

final class CategoryHasArticles extends \DomainException
{
    public function __construct(CategoryId $categoryId, int $articleCount)
    {
        parent::__construct(sprintf('Category %s has %d articles and cannot be deleted', $categoryId->getValue(), $articleCount));
    }

    public static function withId(CategoryId $categoryId): self
    {
        return new self($categoryId, 1);
    }
}
