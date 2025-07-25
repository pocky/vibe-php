<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthor;
use App\BlogContext\Domain\DeleteAuthor\Model\Author as DeleteAuthor;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\UpdateAuthor\Model\Author as UpdateAuthor;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\BlogContext\Infrastructure\Persistence\Mapper\AuthorQueryMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<DoctrineAuthor>
 */
final class AuthorRepository extends ServiceEntityRepository implements AuthorRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly AuthorQueryMapper $queryMapper,
    ) {
        parent::__construct($registry, DoctrineAuthor::class);
    }

    #[\Override]
    public function add(CreateAuthor $author): void
    {
        $entity = new DoctrineAuthor(
            id: Uuid::fromString($author->id()->getValue()),
            name: $author->name()->getValue(),
            email: $author->email()->getValue(),
            bio: $author->bio()->getValue(),
            createdAt: $author->createdAt(),
            updatedAt: $author->updatedAt(),
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function findById(AuthorId $id): CreateAuthor|null
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        return $entity ? $this->queryMapper->mapToCreateModel($entity) : null;
    }

    #[\Override]
    public function findByEmail(AuthorEmail $email): CreateAuthor|null
    {
        $entity = $this->findOneBy([
            'email' => $email->getValue(),
        ]);

        return $entity ? $this->queryMapper->mapToCreateModel($entity) : null;
    }

    #[\Override]
    public function existsById(AuthorId $id): bool
    {
        return null !== $this->find(Uuid::fromString($id->getValue()));
    }

    #[\Override]
    public function existsByEmail(AuthorEmail $email): bool
    {
        return null !== $this->findOneBy([
            'email' => $email->getValue(),
        ]);
    }

    #[\Override]
    public function findAllPaginated(int $limit, int $offset): array
    {
        /** @var list<DoctrineAuthor> $entities */
        $entities = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var CreateAuthor[] */
        return array_map($this->queryMapper->mapToCreateModel(...), $entities);
    }

    #[\Override]
    public function countAll(): int
    {
        /** @var int<0, max> */
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    #[\Override]
    public function update(UpdateAuthor $author): void
    {
        $entity = $this->find(Uuid::fromString($author->id()->getValue()));

        if (!$entity) {
            throw new \RuntimeException('Author not found for update');
        }

        $entity->name = $author->name()->getValue();
        $entity->email = $author->email()->getValue();
        $entity->bio = $author->bio()->getValue();
        $entity->updatedAt = $author->updatedAt();

        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function remove(DeleteAuthor $author): void
    {
        $entity = $this->find(Uuid::fromString($author->id()->getValue()));

        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    #[\Override]
    public function countArticlesByAuthorId(AuthorId $id): int
    {
        /** @var int<0, max> */
        return (int) $this->getEntityManager()
            ->getRepository(DoctrineArticle::class)
            ->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.authorId = :authorId')
            ->setParameter('authorId', $id->getValue())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
