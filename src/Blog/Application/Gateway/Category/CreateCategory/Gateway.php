<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Category\CreateCategory;

use App\Blog\Application\Shared\Exception\ValidationException;
use App\Blog\Application\Shared\Generator\CategoryIdGeneratorInterface;
use App\Blog\Domain\Category\CategoryCreator;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\Service\SlugGeneratorInterface;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class Gateway
{
    public function __construct(
        private CategoryCreator $creator,
        private CategoryIdGeneratorInterface $categoryIdGenerator,
        private SlugGeneratorInterface $slugGenerator,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Validate the request
        $constraintViolationList = $this->validator->validate($request);

        if (0 < count($constraintViolationList)) {
            throw new ValidationException($constraintViolationList);
        }

        // Generate a new category ID
        $categoryId = $this->categoryIdGenerator->nextIdentity();

        // Generate slug if not provided
        $slug = null !== $request->slug && '' !== $request->slug && '0' !== $request->slug
            ? new CategorySlug($request->slug)
            : new CategorySlug($this->slugGenerator->generateFromName(new CategoryName($request->name))->getValue());

        // Prepare parent ID if provided
        $parentId = null !== $request->parentId && '' !== $request->parentId && '0' !== $request->parentId
            ? new CategoryId($request->parentId)
            : null;

        // Prepare order if provided
        $order = null !== $request->order
            ? new Order($request->order)
            : null;

        // Create the category using the domain service
        $category = ($this->creator)(
            categoryId: $categoryId,
            name: new CategoryName($request->name),
            slug: $slug,
            description: new Description($request->description),
            parentId: $parentId,
            order: $order,
        );

        // Return the response
        return new Response(
            id: $category->getId()->getValue(),
            name: $category->getName()->getValue(),
            slug: $category->getSlug()->getValue(),
            description: $category->getDescription()->getValue(),
            parentId: $category->getParentId()?->getValue(),
            order: $category->getOrder()->getValue(),
            createdAt: $category->getCreatedAt(),
        );
    }
}
