<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateCategory;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;
use App\BlogContext\Domain\UpdateCategory\Model\Category;

interface UpdaterInterface
{
    public function __invoke(
        CategoryId $categoryId,
        CategoryName $name,
        CategorySlug $slug,
        Description $description,
        CategoryId|null $parentId,
        Order $order,
    ): Category;
}
