<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Exception;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

final class CategoryNotFound extends \RuntimeException
{
    public function __construct(CategoryId $categoryId)
    {
        parent::__construct(sprintf('Category not found: %s', $categoryId->getValue()));
    }
}
