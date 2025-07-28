<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends DoctrineRepository<DoctrineAuthor>
 *
 * @method DoctrineAuthor|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineAuthor|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineAuthor[] findAll()
 * @method DoctrineAuthor[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AuthorWriteRepository extends DoctrineRepository implements AuthorWriteRepositoryInterface
{
    private const string ALIAS = 'author';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DoctrineAuthor::class, self::ALIAS);
    }

    #[\Override]
    public function add(Author $author): void
    {
        $entity = new DoctrineAuthor(
            id: Uuid::fromString($author->getId()->getValue()),
            name: $author->getName()->getValue(),
            email: $author->getEmail()->getValue(),
            bio: $author->getBio()->getValue(),
            createdAt: $author->getCreatedAt(),
            updatedAt: $author->getUpdatedAt(),
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Author $author): void
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Author not found: %s', $author->getId()->getValue()));
        }

        $entity->name = $author->getName()->getValue();
        $entity->email = $author->getEmail()->getValue();
        $entity->bio = $author->getBio()->getValue();
        $entity->updatedAt = $author->getUpdatedAt();

        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function remove(Author $author): void
    {
        $entity = $this->find(Uuid::fromString($author->getId()->getValue()));

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    #[\Override]
    public function removeById(AuthorId $authorId): void
    {
        $entity = $this->find(Uuid::fromString($authorId->getValue()));

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    #[\Override]
    public function findById(AuthorId $authorId): Author|null
    {
        $entity = $this->find(Uuid::fromString($authorId->getValue()));

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function findByEmail(AuthorEmail $authorEmail): Author|null
    {
        $entity = $this->findOneBy([
            'email' => $authorEmail->getValue(),
        ]);

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function existsById(AuthorId $authorId): bool
    {
        return null !== $this->find(Uuid::fromString($authorId->getValue()));
    }

    #[\Override]
    public function existsByEmail(AuthorEmail $authorEmail): bool
    {
        return null !== $this->findOneBy([
            'email' => $authorEmail->getValue(),
        ]);
    }

    /**
     * Map Doctrine entity to Author aggregate
     */
    private function mapEntityToAggregate(DoctrineAuthor $doctrineAuthor): Author
    {
        // Use reflection to recreate the aggregate since constructor might have business logic
        $reflectionClass = new \ReflectionClass(Author::class);
        $author = $reflectionClass->newInstanceWithoutConstructor();

        // Set properties using reflection
        $reflectionProperty = $reflectionClass->getProperty('authorId');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($author, new AuthorId($doctrineAuthor->id->toRfc4122()));

        $nameProperty = $reflectionClass->getProperty('authorName');
        $nameProperty->setAccessible(true);
        $nameProperty->setValue($author, new AuthorName($doctrineAuthor->name));

        $emailProperty = $reflectionClass->getProperty('authorEmail');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($author, new AuthorEmail($doctrineAuthor->email));

        $bioProperty = $reflectionClass->getProperty('authorBio');
        $bioProperty->setAccessible(true);
        $bioProperty->setValue($author, new AuthorBio($doctrineAuthor->bio));

        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($author, $doctrineAuthor->createdAt);

        $updatedAtProperty = $reflectionClass->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($author, $doctrineAuthor->updatedAt);

        // Initialize events array
        $eventsProperty = $reflectionClass->getProperty('events');
        $eventsProperty->setAccessible(true);
        $eventsProperty->setValue($author, []);

        return $author;
    }
}
