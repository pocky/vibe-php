<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\TagReadModel;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Tag as DoctrineTag;
use App\Blog\Infrastructure\Shared\Mapper\EntityToDomainMapper;

/**
 * Maps Doctrine entity to Tag domain models for query operations.
 *
 * @implements EntityToDomainMapper<DoctrineTag, TagReadModel>
 */
final class TagQueryMapper implements EntityToDomainMapper
{
    public function map(mixed $entity): TagReadModel
    {
        assert($entity instanceof DoctrineTag);

        return new TagReadModel(
            id: new TagId($entity->id->toRfc4122()),
            name: new TagName($entity->name),
            slug: new TagSlug($entity->slug),
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
        );
    }
}
