<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\GetCategory;

final readonly class Query
{
    public function __construct(
        public string|null $categoryId = null,
        public string|null $categorySlug = null,
    ) {
        if (null === $this->categoryId && null === $this->categorySlug) {
            throw new \InvalidArgumentException('Either categoryId or categorySlug must be provided');
        }

        if (null !== $this->categoryId && '' === trim($this->categoryId)) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }

        if (null !== $this->categorySlug && '' === trim($this->categorySlug)) {
            throw new \InvalidArgumentException('Category slug cannot be empty');
        }
    }
}
