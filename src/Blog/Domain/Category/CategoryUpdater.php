<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Slug;

final readonly class CategoryUpdater
{
    public function __construct(
        private CategoryWriteRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(
        CategoryId $categoryId,
        CategoryName|null $name = null,
        CategorySlug|null $slug = null,
        Description|null $description = null,
        CategoryId|null $parentId = null,
        Order|null $order = null,
        bool $clearParent = false,
    ): Category {
        // Retrieve the category from repository
        $category = $this->categoryRepository->get($categoryId);

        // If slug is changing, ensure it's unique
        if ($slug instanceof CategorySlug && !$category->getSlug()->equals($slug)) {
            $genericSlug = new Slug($slug->getValue());
            if ($this->categoryRepository->existsWithSlug($genericSlug)) {
                throw new \DomainException(sprintf('Slug already exists: %s', $slug->getValue()));
            }
        }

        // Update the category using the rich domain model
        $category->update(
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            clearParent: $clearParent,
        );

        // Persist changes
        $this->categoryRepository->update($category);

        return $category;
    }
}
