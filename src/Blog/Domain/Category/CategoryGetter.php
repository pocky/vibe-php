<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Exception\CategoryNotFound;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;

final readonly class CategoryGetter
{
    public function __construct(
        private CategoryReadRepositoryInterface $categoryReadRepository,
    ) {
    }

    public function __invoke(CategoryId|CategorySlug $identifier): CategoryReadModel
    {
        if ($identifier instanceof CategoryId) {
            $readModel = $this->categoryReadRepository->findById($identifier);

            if (!$readModel instanceof CategoryReadModel) {
                throw new CategoryNotFound($identifier);
            }
        } else {
            // CategorySlug
            $readModel = $this->categoryReadRepository->findBySlug($identifier);

            if (!$readModel instanceof CategoryReadModel) {
                // For slug not found, we need to create a CategoryId for the exception
                // Since we don't have the actual ID, we'll use the slug value as ID for the error
                throw new CategoryNotFound(new CategoryId($identifier->getValue()));
            }
        }

        return $readModel;
    }
}
