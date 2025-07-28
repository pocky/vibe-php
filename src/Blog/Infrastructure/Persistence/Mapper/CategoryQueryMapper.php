<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Category as DoctrineCategory;
use App\Blog\Infrastructure\Shared\Mapper\EntityToDomainMapper;

/**
 * Maps Doctrine entity to Category domain models for query operations.
 *
 * @implements EntityToDomainMapper<DoctrineCategory, CategoryReadModel>
 */
final class CategoryQueryMapper implements EntityToDomainMapper
{
    public function map(mixed $entity): CategoryReadModel
    {
        assert($entity instanceof DoctrineCategory);

        return new CategoryReadModel(
            id: new CategoryId($entity->id->toRfc4122()),
            name: new CategoryName($entity->name),
            slug: new CategorySlug($entity->slug),
            description: new Description($entity->description ?? ''),
            parentId: $entity->parentId instanceof \Symfony\Component\Uid\Uuid ? new CategoryId($entity->parentId->toRfc4122()) : null,
            order: new Order($entity->order),
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
        );
    }
}
