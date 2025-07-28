<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Application\Shared\ReadModel\TagReadModel;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Repository\TagReadRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Tag as DoctrineTag;
use App\Blog\Infrastructure\Persistence\Mapper\TagQueryMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for read operations on tags.
 * Returns TagReadModel instances for query operations.
 *
 * @extends DoctrineRepository<DoctrineTag>
 *
 * @method DoctrineTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineTag[] findAll()
 * @method DoctrineTag[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TagReadRepository extends DoctrineRepository implements TagReadRepositoryInterface
{
    private const string ALIAS = 'tag';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly TagQueryMapper $tagQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineTag::class, self::ALIAS);
    }

    public function findById(TagId $tagId): TagReadModel|null
    {
        $entity = $this->find($tagId->getValue());

        return $entity ? $this->tagQueryMapper->map($entity) : null;
    }

    public function findBySlug(TagSlug $tagSlug): TagReadModel|null
    {
        $entity = $this->findOneBy([
            'slug' => $tagSlug->getValue(),
        ]);

        return $entity ? $this->tagQueryMapper->map($entity) : null;
    }

    public function existsBySlug(TagSlug $tagSlug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $tagSlug->getValue(),
        ]);
    }

    /**
     * @return TagReadModel[]
     *
     * @phpstan-ignore-next-line method.childReturnType
     */
    #[\Override]
    public function findAll(): array
    {
        $entities = $this->findBy([], [
            'name' => 'ASC',
        ]);

        return array_map($this->tagQueryMapper->map(...), $entities);
    }

    /**
     * @return TagReadModel[]
     */
    public function findByNamePattern(string $namePattern, int $limit = 10): array
    {
        $entities = $this->createQueryBuilder('t')
            ->where('t.name LIKE :pattern')
            ->setParameter('pattern', '%' . $namePattern . '%')
            ->orderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map($this->tagQueryMapper->map(...), $entities);
    }

    /**
     * @return TagReadModel[]
     */
    public function findUnusedTags(): array
    {
        $entities = $this->createQueryBuilder('t')
            ->where('t.articleCount = 0')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->tagQueryMapper->map(...), $entities);
    }

    /**
     * @return array<array{tag: TagReadModel, articleCount: int}>
     */
    public function findPopularTags(int $limit = 20): array
    {
        $entities = $this->createQueryBuilder('t')
            ->where('t.articleCount > 0')
            ->orderBy('t.articleCount', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(fn (DoctrineTag $doctrineTag): array => [
            'tag' => $this->tagQueryMapper->map($doctrineTag),
            'articleCount' => $doctrineTag->articleCount,
        ], $entities);
    }

    /**
     * @return array{tags: TagReadModel[], total: int}
     */
    public function findPaginated(int $limit, int $offset): array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        // Count total results
        $countQb = clone $queryBuilder;
        $total = (int) $countQb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination and sorting
        $entities = $queryBuilder
            ->orderBy('t.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var TagReadModel[] $tags */
        $tags = array_map($this->tagQueryMapper->map(...), $entities);

        return [
            'tags' => $tags,
            'total' => $total,
        ];
    }

    /**
     * Filter tags by name pattern
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
     * Filter tags with no articles
     */
    public function withNoArticles(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->andWhere(sprintf('%s.articleCount = 0', self::ALIAS));
        });
    }

    /**
     * Filter tags with articles
     */
    public function withArticles(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->andWhere(sprintf('%s.articleCount > 0', self::ALIAS));
        });
    }

    /**
     * Sort by article count descending
     */
    public function withMostPopularFirst(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder
                ->orderBy(sprintf('%s.articleCount', self::ALIAS), 'DESC')
                ->addOrderBy(sprintf('%s.name', self::ALIAS), 'ASC');
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
     * Sort by creation date, newest first
     */
    public function withLatestFirst(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.createdAt', self::ALIAS), 'DESC');
        });
    }

    /**
     * Search by query (name or slug)
     */
    public function withSearchQuery(string $query): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($query): void {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':searchQuery'),
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.slug)', self::ALIAS), ':searchQuery')
                    )
                )
                ->setParameter('searchQuery', '%' . strtolower($query) . '%');
        });
    }

    /**
     * Get results as TagReadModel array
     *
     * @return TagReadModel[]
     */
    public function getReadModels(): array
    {
        $entities = $this->getIterator();
        $results = [];

        foreach ($entities as $entity) {
            $results[] = $this->tagQueryMapper->map($entity);
        }

        return $results;
    }
}
