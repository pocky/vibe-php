<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends DoctrineRepository<DoctrineArticle>
 *
 * @method DoctrineArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineArticle[] findAll()
 * @method DoctrineArticle[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ArticleWriteRepository extends DoctrineRepository implements ArticleWriteRepositoryInterface
{
    private const string ALIAS = 'article';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DoctrineArticle::class, self::ALIAS);
    }

    #[\Override]
    public function add(Article $article): void
    {
        $entity = new DoctrineArticle(
            id: Uuid::fromString($article->id()->getValue()),
            title: $article->title()->getValue(),
            content: $article->content()->getValue(),
            slug: $article->slug()->getValue(),
            status: $article->status()->value,
            excerpt: null, // TODO: Add excerpt() method to Article model
            authorId: $article->authorId()->getValue(),
            categoryId: $article->categoryId()?->getValue(),
            createdAt: $article->createdAt(),
            updatedAt: $article->updatedAt(),
            publishedAt: $article->isPublished() ? $article->updatedAt() : null,
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Article $article): void
    {
        $entity = $this->find(Uuid::fromString($article->id()->getValue()));

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Article not found: %s', $article->id()->getValue()));
        }

        $entity->title = $article->title()->getValue();
        $entity->content = $article->content()->getValue();
        $entity->slug = $article->slug()->getValue();
        $entity->status = $article->status()->value;
        $entity->authorId = $article->authorId()->getValue();
        $entity->categoryId = $article->categoryId()?->getValue();
        $entity->updatedAt = $article->updatedAt();
        $entity->publishedAt = $article->isPublished() ? $article->updatedAt() : null;

        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function get(ArticleId $articleId): Article
    {
        $article = $this->findById($articleId);

        if (!$article instanceof Article) {
            throw new \RuntimeException(sprintf('Article not found: %s', $articleId->getValue()));
        }

        return $article;
    }

    #[\Override]
    public function findById(ArticleId $articleId): Article|null
    {
        $entity = $this->find(Uuid::fromString($articleId->getValue()));

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function findBySlug(Slug $slug): Article|null
    {
        $entity = $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    #[\Override]
    public function remove(Article $article): void
    {
        $entity = $this->find(Uuid::fromString($article->id()->getValue()));

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Map Doctrine entity to Article aggregate
     */
    private function mapEntityToAggregate(DoctrineArticle $doctrineArticle): Article
    {
        // Use reflection to recreate the aggregate since constructor might have business logic
        $reflectionClass = new \ReflectionClass(Article::class);
        $article = $reflectionClass->newInstanceWithoutConstructor();

        // Set properties using reflection
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($article, new ArticleId($doctrineArticle->id->toRfc4122()));

        $titleProperty = $reflectionClass->getProperty('title');
        $titleProperty->setAccessible(true);
        $titleProperty->setValue($article, new Title($doctrineArticle->title));

        $contentProperty = $reflectionClass->getProperty('content');
        $contentProperty->setAccessible(true);
        $contentProperty->setValue($article, new Content($doctrineArticle->content));

        $slugProperty = $reflectionClass->getProperty('slug');
        $slugProperty->setAccessible(true);
        $slugProperty->setValue($article, new Slug($doctrineArticle->slug));

        $statusProperty = $reflectionClass->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($article, ArticleStatus::from($doctrineArticle->status));

        $authorIdProperty = $reflectionClass->getProperty('authorId');
        $authorIdProperty->setAccessible(true);
        $authorIdProperty->setValue($article, new AuthorId($doctrineArticle->authorId));

        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($article, $doctrineArticle->createdAt);

        $updatedAtProperty = $reflectionClass->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($article, $doctrineArticle->updatedAt);

        // Set categoryId if exists
        if (null !== $doctrineArticle->categoryId && '' !== $doctrineArticle->categoryId && '0' !== $doctrineArticle->categoryId) {
            $categoryIdProperty = $reflectionClass->getProperty('categoryId');
            $categoryIdProperty->setAccessible(true);
            $categoryIdProperty->setValue($article, new CategoryId($doctrineArticle->categoryId));
        }

        // Set tagIds (empty array for now, tags relationship not fully implemented)
        $tagIdsProperty = $reflectionClass->getProperty('tagIds');
        $tagIdsProperty->setAccessible(true);
        $tagIdsProperty->setValue($article, []);

        return $article;
    }
}
