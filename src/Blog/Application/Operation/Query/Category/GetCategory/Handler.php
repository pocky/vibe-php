<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\GetCategory;

use App\Blog\Domain\Category\CategoryGetter;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;

final readonly class Handler
{
    public function __construct(
        private CategoryGetter $getter,
    ) {
    }

    public function __invoke(Query $query): View
    {
        // Determine which identifier to use and call the domain service
        if (null !== $query->categoryId) {
            $categoryId = new CategoryId($query->categoryId);
            $categoryReadModel = ($this->getter)($categoryId);
        } else {
            // Use slug
            $categorySlug = new CategorySlug($query->categorySlug);
            $categoryReadModel = ($this->getter)($categorySlug);
        }

        return new View($categoryReadModel);
    }
}
