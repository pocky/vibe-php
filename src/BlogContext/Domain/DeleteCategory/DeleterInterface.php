<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteCategory;

use App\BlogContext\Domain\DeleteCategory\Model\Category;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

interface DeleterInterface
{
    public function __invoke(
        CategoryId $categoryId,
    ): Category;
}
