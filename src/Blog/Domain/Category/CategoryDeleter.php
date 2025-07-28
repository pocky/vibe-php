<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;

final readonly class CategoryDeleter
{
    public function __construct(
        private CategoryWriteRepositoryInterface $categoryRepository,
        private CategoryReadRepositoryInterface $categoryReadRepository,
    ) {
    }

    public function __invoke(CategoryId $categoryId): void
    {
        // Retrieve the category from repository
        $category = $this->categoryRepository->get($categoryId);

        // Count children and articles for business rule validation
        $children = $this->categoryReadRepository->findByParentId($categoryId);
        $childCount = count($children);

        $articleCount = 0;
        if (0 === $childCount) {
            // Only check articles if no children (fail fast on children)
            $articleCount = $this->categoryReadRepository->countArticlesByCategory($categoryId);
        }

        // Use the domain model's delete method which enforces business rules
        $category->delete($childCount, $articleCount);

        // Physically remove the category
        $this->categoryRepository->remove($category);
    }
}
