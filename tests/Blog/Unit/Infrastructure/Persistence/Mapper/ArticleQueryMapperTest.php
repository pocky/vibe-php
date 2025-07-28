<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\Blog\Infrastructure\Persistence\Mapper\ArticleQueryMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleQueryMapperTest extends TestCase
{
    private ArticleQueryMapper $articleQueryMapper;

    protected function setUp(): void
    {
        $this->articleQueryMapper = new ArticleQueryMapper();
    }

    public function testMapCreatesArticleReadModelFromDoctrineEntity(): void
    {
        // Arrange
        $uuidV7 = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 10:00:00');

        $article = new DoctrineArticle(
            id: $uuidV7,
            title: 'Test Article',
            content: 'Test content',
            slug: 'test-article',
            status: 'draft',
            excerpt: 'Test excerpt',
            authorId: 'author-123',
            categoryId: null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishedAt: null
        );

        // Act
        $articleReadModel = $this->articleQueryMapper->map($article);

        // Assert
        $this->assertInstanceOf(ArticleReadModel::class, $articleReadModel);
        $this->assertSame($uuidV7->toRfc4122(), $articleReadModel->id->getValue());
        $this->assertSame('Test Article', $articleReadModel->title->getValue());
        $this->assertSame('Test content', $articleReadModel->content->getValue());
        $this->assertSame('test-article', $articleReadModel->slug->getValue());
        $this->assertSame(ArticleStatus::DRAFT, $articleReadModel->status);
        $this->assertSame('author-123', $articleReadModel->authorId);
        $this->assertEquals($createdAt, $articleReadModel->timestamps->getCreatedAt());
        $this->assertEquals($updatedAt, $articleReadModel->timestamps->getUpdatedAt());
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $articleReadModel->publishedAt);
    }

    public function testMapHandlesPublishedArticle(): void
    {
        // Arrange
        $uuidV7 = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 11:00:00');
        $publishedAt = new \DateTimeImmutable('2024-01-20 11:00:00');

        $article = new DoctrineArticle(
            id: $uuidV7,
            title: 'Published Article',
            content: 'Published content',
            slug: 'published-article',
            status: 'published',
            excerpt: 'Published excerpt',
            authorId: 'author-123',
            categoryId: null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishedAt: $publishedAt
        );

        // Act
        $articleReadModel = $this->articleQueryMapper->map($article);

        // Assert
        $this->assertSame(ArticleStatus::PUBLISHED, $articleReadModel->status);
        $this->assertEquals($publishedAt, $articleReadModel->publishedAt);
    }
}
