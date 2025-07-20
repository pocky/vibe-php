<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Infrastructure\Persistence\Mapper;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\BlogContext\Infrastructure\Persistence\Mapper\ArticleQueryMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleQueryMapperTest extends TestCase
{
    private ArticleQueryMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ArticleQueryMapper();
    }

    public function testMapCreatesArticleReadModelFromDoctrineEntity(): void
    {
        // Arrange
        $id = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 10:00:00');

        $entity = new DoctrineArticle(
            id: $id,
            title: 'Test Article',
            content: 'Test content',
            slug: 'test-article',
            status: 'draft',
            authorId: 'author-123',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishedAt: null
        );

        // Act
        $readModel = $this->mapper->map($entity);

        // Assert
        $this->assertInstanceOf(ArticleReadModel::class, $readModel);
        $this->assertEquals($id->toRfc4122(), $readModel->id->getValue());
        $this->assertEquals('Test Article', $readModel->title->getValue());
        $this->assertEquals('Test content', $readModel->content->getValue());
        $this->assertEquals('test-article', $readModel->slug->getValue());
        $this->assertEquals(ArticleStatus::DRAFT, $readModel->status);
        $this->assertEquals('author-123', $readModel->authorId);
        $this->assertEquals($createdAt, $readModel->timestamps->getCreatedAt());
        $this->assertEquals($updatedAt, $readModel->timestamps->getUpdatedAt());
        $this->assertNull($readModel->publishedAt);
    }

    public function testMapHandlesPublishedArticle(): void
    {
        // Arrange
        $id = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 11:00:00');
        $publishedAt = new \DateTimeImmutable('2024-01-20 11:00:00');

        $entity = new DoctrineArticle(
            id: $id,
            title: 'Published Article',
            content: 'Published content',
            slug: 'published-article',
            status: 'published',
            authorId: 'author-123',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishedAt: $publishedAt
        );

        // Act
        $readModel = $this->mapper->map($entity);

        // Assert
        $this->assertEquals(ArticleStatus::PUBLISHED, $readModel->status);
        $this->assertEquals($publishedAt, $readModel->publishedAt);
    }
}
