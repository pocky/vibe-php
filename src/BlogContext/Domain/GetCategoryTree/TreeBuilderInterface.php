<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategoryTree;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

interface TreeBuilderInterface
{
    public function __invoke(CategoryId|null $rootId, int $maxDepth): TreeData;
}
