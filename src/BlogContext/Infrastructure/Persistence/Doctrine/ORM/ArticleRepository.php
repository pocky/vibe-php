<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\ArticleMappingRegistry;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\EntityToArticleMapper;
use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleMappingRegistry $mappingRegistry,
        private EntityToArticleMapper $entityToArticleMapper,
    ) {
    }

    public function save(object $article): void
    {
        // Get the article ID to find existing entity
        $articleId = $this->extractArticleId($article);
        $existingEntity = null;

        if ($articleId instanceof ArticleId) {
            $existingEntity = $this->entityManager->find(
                BlogArticle::class,
                Uuid::fromString($articleId->getValue())
            );
        }

        // Use the mapping registry to handle the conversion
        $entity = $this->mappingRegistry->mapToEntity($article, $existingEntity);

        // Persist new entities
        if (!$existingEntity instanceof BlogArticle) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * Extract ArticleId from various domain article types
     */
    private function extractArticleId(object $article): ArticleId|null
    {
        // Check common property names
        if (property_exists($article, 'id') && $article->id instanceof ArticleId) {
            return $article->id;
        }

        // Check for getter methods
        if (method_exists($article, 'getArticleId')) {
            /** @var ArticleId */
            return $article->getArticleId();
        }

        if (method_exists($article, 'getId')) {
            /** @var ArticleId */
            return $article->getId();
        }

        return null;
    }

    public function findById(ArticleId $id): Article|null
    {
        $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($id->toString()));

        if (null === $entity) {
            return null;
        }

        return $this->entityToArticleMapper->mapToDomain($entity);
    }

    public function existsBySlug(Slug $slug): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id)')
            ->from(BlogArticle::class, 'a')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug->getValue());

        return 0 < $qb->getQuery()->getSingleScalarResult();
    }

    public function remove(object $article): void
    {
        $articleId = $this->extractArticleId($article);

        if ($articleId instanceof ArticleId) {
            $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($articleId->getValue()));
            if ($entity instanceof BlogArticle) {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
            }
        }
    }

    public function findAllPaginated(int $page, int $limit, array $filters = []): PaginatorInterface
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(BlogArticle::class, 'a')
            ->orderBy('a.createdAt', 'DESC');

        // Apply status filter
        if (isset($filters['status'])) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $filters['status']);
        }

        // Calculate offset
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $entities = $qb->getQuery()->getResult();

        // Get total count
        $countQb = $this->entityManager->createQueryBuilder();
        $countQb->select('COUNT(a.id)')
            ->from(BlogArticle::class, 'a');

        if (isset($filters['status'])) {
            $countQb->andWhere('a.status = :status')
                ->setParameter('status', $filters['status']);
        }

        $total = $countQb->getQuery()->getSingleScalarResult();

        // Convert entities to domain models
        $items = array_map(
            fn (BlogArticle $entity) => $this->entityToArticleMapper->mapToDomain($entity),
            $entities
        );

        return new readonly class($items, $total, $page, $limit) implements PaginatorInterface {
            public function __construct(
                private array $items,
                private int $total,
                private int $page,
                private int $limit,
            ) {
            }

            public function getItems(): array
            {
                return $this->items;
            }

            public function getTotalItems(): int
            {
                return $this->total;
            }

            public function getCurrentPage(): int
            {
                return $this->page;
            }

            public function getItemsPerPage(): int
            {
                return $this->limit;
            }

            public function hasNextPage(): bool
            {
                return ($this->page * $this->limit) < $this->total;
            }
        };
    }
}
