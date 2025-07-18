<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

interface CreatorInterface
{
    public function __invoke(
        CategoryId $categoryId,
        CategoryName $name,
        CategorySlug $slug,
        CategoryId|null $parentId = null,
        \DateTimeImmutable|null $createdAt = null,
    ): Category;
}
