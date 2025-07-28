<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Exception;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

final class CategoryHasChildren extends \DomainException
{
    public function __construct(CategoryId $categoryId, int $childCount)
    {
        parent::__construct(sprintf('Category %s has %d children and cannot be deleted', $categoryId->getValue(), $childCount));
    }

    public static function withId(CategoryId $categoryId): self
    {
        return new self($categoryId, 1);
    }
}
