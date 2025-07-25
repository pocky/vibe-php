<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Mapper;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthor;
use App\BlogContext\Domain\Shared\Mapper\EntityToDomainMapper;
use App\BlogContext\Domain\Shared\ReadModel\AuthorReadModel;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;

/**
 * Maps Doctrine entity to Author domain models for query operations.
 *
 * @implements EntityToDomainMapper<DoctrineAuthor, AuthorReadModel>
 */
final class AuthorQueryMapper implements EntityToDomainMapper
{
    public function map(mixed $entity): AuthorReadModel
    {
        assert($entity instanceof DoctrineAuthor);

        return new AuthorReadModel(
            id: new AuthorId($entity->id->toRfc4122()),
            name: new AuthorName($entity->name),
            email: new AuthorEmail($entity->email),
            bio: new AuthorBio($entity->bio),
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
        );
    }

    /**
     * Maps Doctrine entity to CreateAuthor domain model.
     */
    public function mapToCreateModel(DoctrineAuthor $entity): CreateAuthor
    {
        $author = CreateAuthor::create(
            id: new AuthorId($entity->id->toRfc4122()),
            name: new AuthorName($entity->name),
            email: new AuthorEmail($entity->email),
            bio: new AuthorBio($entity->bio),
            createdAt: $entity->createdAt,
        );

        // Clear events since this is coming from persistence
        return $author->withEvents([]);
    }
}
