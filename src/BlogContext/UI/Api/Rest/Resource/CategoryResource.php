<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\BlogContext\UI\Api\Rest\Processor\CreateCategoryProcessor;
use App\BlogContext\UI\Api\Rest\Provider\GetCategoryProvider;
use App\BlogContext\UI\Api\Rest\Provider\ListCategoriesProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Category',
    operations: [
        new Get(
            uriTemplate: '/categories/{id}',
            provider: GetCategoryProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/categories',
            provider: ListCategoriesProvider::class,
        ),
        new Post(
            uriTemplate: '/categories',
            processor: CreateCategoryProcessor::class,
        ),
    ],
)]
final class CategoryResource
{
    public function __construct(
        public string|null $id = null,

        // TODO: Add resource properties with validation
        // Example:
        // #[Assert\NotBlank(groups: ['create'])]
        // #[Assert\Length(min: 3, max: 200)]
        // public ?string $title = null,

        // #[Assert\NotBlank(groups: ['create'])]
        // public ?string $content = null,

        public \DateTimeImmutable|null $createdAt = null,
        public \DateTimeImmutable|null $updatedAt = null,
    ) {
    }
}
