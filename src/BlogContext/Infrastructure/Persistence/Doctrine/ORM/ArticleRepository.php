<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\Shared\Repository\ArticleData;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(object $article): void
    {
        // Support different types of article domain objects
        if ($article instanceof \App\BlogContext\Domain\CreateArticle\DataPersister\Article) {
            // Check if entity already exists
            $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($article->id->getValue()));

            if (!$entity instanceof BlogArticle) {
                // Create new entity
                $entity = new BlogArticle(
                    id: Uuid::fromString($article->id->getValue()),
                    title: $article->title->getValue(),
                    content: $article->content->getValue(),
                    slug: $article->slug->getValue(),
                    status: $article->status->value,
                    createdAt: $article->createdAt,
                    // updatedAt is null for new articles
                );
                $this->entityManager->persist($entity);
            } else {
                // Update existing entity
                $entity->setTitle($article->title->getValue());
                $entity->setContent($article->content->getValue());
                $entity->setSlug($article->slug->getValue());
                $entity->setStatus($article->status->value);
                $entity->setUpdatedAt(new \DateTimeImmutable());
            }
        } elseif ($article instanceof \App\BlogContext\Domain\UpdateArticle\DataPersister\Article) {
            $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($article->id->getValue()));
            if ($entity instanceof BlogArticle) {
                $entity->setTitle($article->title->getValue());
                $entity->setContent($article->content->getValue());
                $entity->setSlug($article->slug->getValue());
                $entity->setStatus($article->status->value);
                $entity->setUpdatedAt($article->updatedAt);
            }
        } elseif ($article instanceof \App\BlogContext\Domain\PublishArticle\DataPersister\Article) {
            $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($article->id->getValue()));
            if ($entity instanceof BlogArticle) {
                $entity->setStatus($article->status->value);
                $entity->setPublishedAt($article->publishedAt);
                $entity->setUpdatedAt(new \DateTimeImmutable());
            }
        } else {
            throw new \InvalidArgumentException('Unsupported article type: ' . $article::class);
        }

        $this->entityManager->flush();
    }

    public function findById(ArticleId $id): ArticleData|null
    {
        $entity = $this->entityManager->find(BlogArticle::class, Uuid::fromString($id->toString()));

        if (null === $entity) {
            return null;
        }

        return new ArticleData(
            id: new ArticleId($entity->getId()->toRfc4122()),
            title: new Title($entity->getTitle()),
            content: new Content($entity->getContent()),
            slug: new Slug($entity->getSlug()),
            status: ArticleStatus::fromString($entity->getStatus()),
            createdAt: $entity->getCreatedAt(),
            publishedAt: $entity->getPublishedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
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
        // Get the article ID from any of the domain article types
        $articleId = null;

        // With PHP 8.4 property hooks, we only need to check property_exists
        if (property_exists($article, 'id')) {
            /** @var object{id: ArticleId} $article */
            $articleId = $article->id;
        }

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

        // Convert entities to arrays for View layer
        $items = array_map(fn (BlogArticle $entity) => [
            'id' => $entity->getId()->toRfc4122(),
            'title' => $entity->getTitle(),
            'content' => $entity->getContent(),
            'slug' => $entity->getSlug(),
            'status' => $entity->getStatus(),
            'created_at' => $entity->getCreatedAt()->format('c'),
            'updated_at' => $entity->getUpdatedAt()?->format('c') ?? '',
            'published_at' => $entity->getPublishedAt()?->format('c'),
        ], $entities);

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
