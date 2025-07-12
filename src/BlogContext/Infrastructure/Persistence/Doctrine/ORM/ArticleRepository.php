<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article as CreateArticle;
use App\BlogContext\Domain\PublishArticle\DataPersister\Article as PublishArticle;
use App\BlogContext\Domain\Shared\Repository\{ArticleData, ArticleRepositoryInterface};
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Domain\UpdateArticle\DataPersister\Article as UpdateArticle;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class ArticleRepository extends DoctrineRepository implements ArticleRepositoryInterface
{
    private const string ALIAS = 'article';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogArticle::class, self::ALIAS);
    }

    #[\Override]
    public function save(object $article): void
    {
        // Handle polymorphic article models
        if ($article instanceof CreateArticle) {
            $this->saveCreateArticle($article);
        } elseif ($article instanceof UpdateArticle) {
            $this->saveUpdateArticle($article);
        } elseif ($article instanceof PublishArticle) {
            $this->savePublishArticle($article);
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported article type: %s', $article::class));
        }
    }

    #[\Override]
    public function findById(ArticleId $id): ArticleData|null
    {
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->where('article.id = :id')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('id', Uuid::fromString($id->getValue()))]));

        /** @var BlogArticle|null $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

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
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    #[\Override]
    public function existsBySlug(Slug $slug): bool
    {
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->select('COUNT(article.id)')
            ->where('article.slug = :slug')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('slug', $slug->getValue())]));

        $count = $qb->getQuery()->getSingleScalarResult();

        return 0 < $count;
    }

    #[\Override]
    public function remove(object $article): void
    {
        // Extract ID based on article type
        $articleId = match (true) {
            $article instanceof CreateArticle => $article->id,
            $article instanceof UpdateArticle => $article->id,
            $article instanceof PublishArticle => $article->id,
            default => throw new \InvalidArgumentException(sprintf('Unsupported article type for removal: %s', $article::class)),
        };

        $qb = $this->createQueryBuilder(self::ALIAS)
            ->where('article.id = :id')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('id', Uuid::fromString($articleId->getValue()))]));

        /** @var BlogArticle|null $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    private function saveCreateArticle(CreateArticle $article): void
    {
        // Check if entity already exists
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->where('article.id = :id')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('id', Uuid::fromString($article->id->getValue()))]));

        /** @var BlogArticle|null $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (null === $entity) {
            // Create new entity
            $entity = new BlogArticle(
                id: Uuid::fromString($article->id->getValue()),
                title: $article->title->getValue(),
                content: $article->content->getValue(),
                slug: $article->slug->getValue(),
                status: $article->status->getValue(),
                createdAt: $article->createdAt,
                publishedAt: null,
                updatedAt: null,
            );

            $this->getEntityManager()->persist($entity);
        } else {
            // Update existing entity
            $entity->setTitle($article->title->getValue());
            $entity->setContent($article->content->getValue());
            $entity->setSlug($article->slug->getValue());
            $entity->setStatus($article->status->getValue());
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->getEntityManager()->flush();
    }

    private function saveUpdateArticle(UpdateArticle $article): void
    {
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->where('article.id = :id')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('id', Uuid::fromString($article->id->getValue()))]));

        /** @var BlogArticle|null $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Article with ID %s not found for update', $article->id->getValue()));
        }

        $entity->setTitle($article->title->getValue());
        $entity->setContent($article->content->getValue());
        $entity->setSlug($article->slug->getValue());
        $entity->setStatus($article->status->getValue());
        $entity->setUpdatedAt($article->updatedAt);

        $this->getEntityManager()->flush();
    }

    private function savePublishArticle(PublishArticle $article): void
    {
        $qb = $this->createQueryBuilder(self::ALIAS)
            ->where('article.id = :id')
            ->setParameters(new ArrayCollection([new \Doctrine\ORM\Query\Parameter('id', Uuid::fromString($article->id->getValue()))]));

        /** @var BlogArticle|null $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Article with ID %s not found for publication', $article->id->getValue()));
        }

        $entity->setStatus($article->status->getValue());
        $entity->setPublishedAt($article->publishedAt);
        $entity->setUpdatedAt(new \DateTimeImmutable());

        $this->getEntityManager()->flush();
    }
}
