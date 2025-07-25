<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategory;

use App\BlogContext\Domain\GetCategory\Model\Category;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

interface GetterInterface
{
    public function __invoke(CategoryId $categoryId): Category;
}
