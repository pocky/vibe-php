<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\Blog\Infrastructure\Persistence\Mapper\AuthorQueryMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for read operations on authors.
 * Returns AuthorReadModel instances for query operations.
 *
 * @extends DoctrineRepository<DoctrineAuthor>
 *
 * @method DoctrineAuthor|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineAuthor|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineAuthor[] findAll()
 * @method DoctrineAuthor[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AuthorReadRepository extends DoctrineRepository implements AuthorReadRepositoryInterface
{
    private const string ALIAS = 'author';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly AuthorQueryMapper $authorQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineAuthor::class, self::ALIAS);
    }

    public function findById(AuthorId $authorId): AuthorReadModel|null
    {
        $entity = $this->find($authorId->getValue());

        return $entity ? $this->authorQueryMapper->map($entity) : null;
    }

    public function findByEmail(AuthorEmail $authorEmail): AuthorReadModel|null
    {
        $entity = $this->findOneBy([
            'email' => $authorEmail->getValue(),
        ]);

        return $entity ? $this->authorQueryMapper->map($entity) : null;
    }

    public function existsById(AuthorId $authorId): bool
    {
        return null !== $this->find($authorId->getValue());
    }

    public function existsByEmail(AuthorEmail $authorEmail): bool
    {
        return null !== $this->findOneBy([
            'email' => $authorEmail->getValue(),
        ]);
    }

    /**
     * @return array{authors: AuthorReadModel[], total: int}
     */
    public function findAllPaginated(int $limit, int $offset): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        // Count total results
        $countQb = clone $queryBuilder;
        $total = (int) $countQb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination and sorting
        $entities = $queryBuilder
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var AuthorReadModel[] $authors */
        $authors = array_map($this->authorQueryMapper->map(...), $entities);

        return [
            'authors' => $authors,
            'total' => $total,
        ];
    }

    /**
     * @return AuthorReadModel[]
     */
    public function searchByName(string $namePattern, int $limit = 10): array
    {
        $entities = $this->createQueryBuilder('a')
            ->where('a.name LIKE :pattern')
            ->setParameter('pattern', '%' . $namePattern . '%')
            ->orderBy('a.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map($this->authorQueryMapper->map(...), $entities);
    }

    /**
     * Filter authors by name pattern
     */
    public function withNameLike(string $pattern): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($pattern): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':namePattern'))
                ->setParameter('namePattern', '%' . strtolower($pattern) . '%');
        });
    }

    /**
     * Filter authors by email pattern
     */
    public function withEmailLike(string $pattern): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($pattern): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like(sprintf('LOWER(%s.email)', self::ALIAS), ':emailPattern'))
                ->setParameter('emailPattern', '%' . strtolower($pattern) . '%');
        });
    }

    /**
     * Sort by name ascending
     */
    public function withNameAscending(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.name', self::ALIAS), 'ASC');
        });
    }

    /**
     * Sort by name descending
     */
    public function withNameDescending(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.name', self::ALIAS), 'DESC');
        });
    }

    /**
     * Sort by creation date, newest first
     */
    public function withLatestFirst(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.createdAt', self::ALIAS), 'DESC');
        });
    }

    /**
     * Filter authors created after a specific date
     */
    public function withCreatedAfter(\DateTimeImmutable $date): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($date): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte(sprintf('%s.createdAt', self::ALIAS), ':createdAfter'))
                ->setParameter('createdAfter', $date);
        });
    }

    /**
     * Search by query (name or email)
     */
    public function withSearchQuery(string $query): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($query): void {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':searchQuery'),
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.email)', self::ALIAS), ':searchQuery')
                    )
                )
                ->setParameter('searchQuery', '%' . strtolower($query) . '%');
        });
    }
}
