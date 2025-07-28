<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\Blog\Infrastructure\Shared\Mapper\EntityToDomainMapper;

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
     * Maps Doctrine entity to Author domain model.
     */
    public function mapToCreateModel(DoctrineAuthor $doctrineAuthor): Author
    {
        return new Author(
            authorId: new AuthorId($doctrineAuthor->id->toRfc4122()),
            authorName: new AuthorName($doctrineAuthor->name),
            authorEmail: new AuthorEmail($doctrineAuthor->email),
            authorBio: new AuthorBio($doctrineAuthor->bio),
            createdAt: $doctrineAuthor->createdAt,
            updatedAt: $doctrineAuthor->updatedAt,
        );
    }
}
