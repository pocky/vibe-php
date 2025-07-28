<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Repository\ArticleReadRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\Blog\Infrastructure\Persistence\Mapper\ArticleQueryMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for read operations on articles.
 * Returns ArticleReadModel instances for query operations.
 *
 * @extends DoctrineRepository<DoctrineArticle>
 *
 * @method DoctrineArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineArticle[] findAll()
 * @method DoctrineArticle[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ArticleReadRepository extends DoctrineRepository implements ArticleReadRepositoryInterface
{
    private const string ALIAS = 'article';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly ArticleQueryMapper $articleQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineArticle::class, self::ALIAS);
    }

    public function findById(ArticleId $articleId): ArticleReadModel|null
    {
        $entity = $this->find($articleId->getValue());

        return $entity ? $this->articleQueryMapper->map($entity) : null;
    }

    public function findBySlug(Slug $slug): ArticleReadModel|null
    {
        $entity = $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);

        return $entity ? $this->articleQueryMapper->map($entity) : null;
    }

    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    /**
     * @return array{articles: ArticleReadModel[], total: int}
     */
    public function findByCriteria(
        ArticleStatus|null $status = null,
        string|null $authorId = null,
        int $limit = 20,
        int $offset = 0,
        string $sortBy = 'createdAt',
        string $sortOrder = 'DESC',
    ): array {
        $queryBuilder = $this->createQueryBuilder('a');

        // Apply filters
        if ($status instanceof ArticleStatus) {
            $queryBuilder->andWhere('a.status = :status')
                ->setParameter('status', $status->value);
        }

        if (null !== $authorId) {
            $queryBuilder->andWhere('a.authorId = :authorId')
                ->setParameter('authorId', $authorId);
        }

        // Count total results
        $countQb = clone $queryBuilder;
        $total = (int) $countQb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Apply sorting
        $queryBuilder->orderBy('a.' . $sortBy, $sortOrder);

        // Apply pagination
        $entities = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var ArticleReadModel[] $articles */
        $articles = array_map($this->articleQueryMapper->map(...), $entities);

        return [
            'articles' => $articles,
            'total' => $total,
        ];
    }

    /**
     * @return array<array{id: string, title: string, slug: string, status: string, publishedAt: string|null}>
     */
    public function findByAuthorId(AuthorId $authorId, int $limit, int $offset): array
    {
        $entities = $this->createQueryBuilder('a')
            ->where('a.authorId = :authorId')
            ->setParameter('authorId', $authorId->getValue())
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(fn (DoctrineArticle $doctrineArticle): array => [
            'id' => $doctrineArticle->id->__toString(),
            'title' => $doctrineArticle->title,
            'slug' => $doctrineArticle->slug,
            'status' => $doctrineArticle->status,
            'publishedAt' => $doctrineArticle->publishedAt?->format(\DateTimeInterface::ATOM),
        ], $entities);
    }

    public function countByAuthorId(AuthorId $authorId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.authorId = :authorId')
            ->setParameter('authorId', $authorId->getValue())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Filter articles by status
     */
    public function withStatus(ArticleStatus $status): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($status): void {
            $queryBuilder
                ->andWhere(sprintf('%s.status = :status', self::ALIAS))
                ->setParameter('status', $status->value);
        });
    }

    /**
     * Filter published articles only
     */
    public function withPublishedOnly(): static
    {
        return $this->withStatus(ArticleStatus::PUBLISHED);
    }

    /**
     * Filter draft articles only
     */
    public function withDraftOnly(): static
    {
        return $this->withStatus(ArticleStatus::DRAFT);
    }

    /**
     * Filter articles by author
     */
    public function withAuthor(AuthorId $authorId): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($authorId): void {
            $queryBuilder
                ->andWhere(sprintf('%s.authorId = :authorId', self::ALIAS))
                ->setParameter('authorId', $authorId->getValue());
        });
    }

    /**
     * Filter articles by category
     */
    public function withCategory(string $categoryId): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($categoryId): void {
            $queryBuilder
                ->andWhere(sprintf('%s.categoryId = :categoryId', self::ALIAS))
                ->setParameter('categoryId', $categoryId);
        });
    }

    /**
     * Filter articles by title pattern
     */
    public function withTitleLike(string $pattern): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($pattern): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like(sprintf('LOWER(%s.title)', self::ALIAS), ':titlePattern'))
                ->setParameter('titlePattern', '%' . strtolower($pattern) . '%');
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
     * Sort by publication date, newest first
     */
    public function withLatestPublishedFirst(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.publishedAt', self::ALIAS), 'DESC');
        });
    }

    /**
     * Filter articles published after a specific date
     */
    public function withPublishedAfter(\DateTimeImmutable $date): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($date): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte(sprintf('%s.publishedAt', self::ALIAS), ':publishedAfter'))
                ->setParameter('publishedAfter', $date);
        });
    }

    /**
     * Filter articles published before a specific date
     */
    public function withPublishedBefore(\DateTimeImmutable $date): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($date): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte(sprintf('%s.publishedAt', self::ALIAS), ':publishedBefore'))
                ->setParameter('publishedBefore', $date);
        });
    }

    /**
     * Search by query (title or content)
     */
    public function withSearchQuery(string $query): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($query): void {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.title)', self::ALIAS), ':searchQuery'),
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.content)', self::ALIAS), ':searchQuery')
                    )
                )
                ->setParameter('searchQuery', '%' . strtolower($query) . '%');
        });
    }

    /**
     * Get results as ArticleReadModel array
     *
     * @return ArticleReadModel[]
     */
    public function getReadModels(): array
    {
        $entities = $this->getIterator();
        $results = [];

        foreach ($entities as $entity) {
            $results[] = $this->articleQueryMapper->map($entity);
        }

        return $results;
    }
}
