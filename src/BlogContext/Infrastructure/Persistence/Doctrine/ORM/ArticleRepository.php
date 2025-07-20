<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\CreateArticle\Model\Article as CreateArticle;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\UpdateArticle\Model\Article as UpdateArticle;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\BlogContext\Infrastructure\Persistence\Mapper\ArticleQueryMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<DoctrineArticle>
 */
final class ArticleRepository extends ServiceEntityRepository implements ArticleRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ArticleQueryMapper $queryMapper,
    ) {
        parent::__construct($registry, DoctrineArticle::class);
    }

    public function add(CreateArticle $article): void
    {
        $entity = new DoctrineArticle(
            id: Uuid::fromString($article->id->getValue()),
            title: $article->title->getValue(),
            content: $article->content->getValue(),
            slug: $article->slug->getValue(),
            status: $article->status->value,
            authorId: $article->authorId,
            createdAt: $article->timestamps->getCreatedAt(),
            updatedAt: $article->timestamps->getUpdatedAt(),
            publishedAt: null,
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function update(UpdateArticle $article): void
    {
        $entity = $this->find(Uuid::fromString($article->id->getValue()));

        if (!$entity) {
            throw new \RuntimeException('Article not found for update');
        }

        $entity->title = $article->title->getValue();
        $entity->content = $article->content->getValue();
        $entity->slug = $article->slug->getValue();
        $entity->status = $article->status->value;
        $entity->updatedAt = $article->timestamps->getUpdatedAt();

        if ($article->publishedAt instanceof \DateTimeImmutable) {
            $entity->publishedAt = $article->publishedAt;
        }

        $this->getEntityManager()->flush();
    }

    public function findById(ArticleId $id): ArticleReadModel|null
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        return $entity ? $this->queryMapper->map($entity) : null;
    }

    public function findBySlug(Slug $slug): ArticleReadModel|null
    {
        $entity = $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);

        return $entity ? $this->queryMapper->map($entity) : null;
    }

    public function remove(ArticleId $id): void
    {
        $this->delete($id);
    }

    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    /**
     * @return ArticleReadModel[]
     */
    #[\Override]
    public function findAllPaginated(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        /** @var list<DoctrineArticle> $entities */
        $entities = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var ArticleReadModel[] */
        return array_values(array_map($this->queryMapper->map(...), $entities));
    }

    #[\Override]
    public function count(array $criteria = []): int
    {
        if ([] === $criteria) {
            /** @var int<0, max> */
            return (int) $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        /** @var int<0, max> */
        return parent::count($criteria);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    #[\Override]
    public function findByFilters(
        array $filters,
        string|null $sortBy = null,
        string $sortOrder = 'asc',
        int $limit = 20,
        int $offset = 0
    ): array {
        $qb = $this->createQueryBuilder('a');

        // Apply filters
        if (null !== ($filters['status'] ?? null)) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (null !== ($filters['authorId'] ?? null)) {
            $qb->andWhere('a.authorId = :authorId')
                ->setParameter('authorId', $filters['authorId']);
        }

        // Apply sorting
        if (null !== $sortBy) {
            $qb->orderBy('a.' . $sortBy, $sortOrder);
        } else {
            $qb->orderBy('a.createdAt', 'DESC');
        }

        // Apply pagination
        $entities = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map($this->queryMapper->map(...), $entities);
    }

    #[\Override]
    public function countByFilters(array $filters): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)');

        // Apply filters
        if (null !== ($filters['status'] ?? null)) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (null !== ($filters['authorId'] ?? null)) {
            $qb->andWhere('a.authorId = :authorId')
                ->setParameter('authorId', $filters['authorId']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array{articles: ArticleReadModel[], total: int}
     */
    #[\Override]
    public function findByCriteria(
        ArticleStatus|null $status = null,
        string|null $authorId = null,
        int $limit = 20,
        int $offset = 0,
        string $sortBy = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('a');

        // Apply filters
        if ($status instanceof ArticleStatus) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $status->value);
        }

        if (null !== $authorId) {
            $qb->andWhere('a.authorId = :authorId')
                ->setParameter('authorId', $authorId);
        }

        // Count total results
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Apply sorting
        $qb->orderBy('a.' . $sortBy, $sortOrder);

        // Apply pagination
        $entities = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var ArticleReadModel[] $articles */
        $articles = array_map($this->queryMapper->map(...), $entities);

        return [
            'articles' => $articles,
            'total' => $total,
        ];
    }

    private function delete(ArticleId $id): void
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }
}
