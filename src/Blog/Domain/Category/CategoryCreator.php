<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category;

use App\Blog\Domain\Category\Shared\Exception\CategoryAlreadyExists;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Slug;

final readonly class CategoryCreator
{
    public function __construct(
        private CategoryWriteRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(
        CategoryId $categoryId,
        CategoryName $name,
        CategorySlug $slug,
        Description $description,
        CategoryId|null $parentId = null,
        Order|null $order = null,
    ): Category {
        // Verify slug uniqueness - convert CategorySlug to Slug for repository check
        $genericSlug = new Slug($slug->getValue());
        if ($this->categoryRepository->existsWithSlug($genericSlug)) {
            throw new CategoryAlreadyExists($categoryId->getValue());
        }

        // Create the category using the rich domain model
        $category = Category::create(
            id: $categoryId,
            categoryName: $name,
            categorySlug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
        );

        // Persist the category
        $this->categoryRepository->add($category);

        // Return the category with its events
        return $category;
    }
}
