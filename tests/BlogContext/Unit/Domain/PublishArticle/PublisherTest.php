<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\PublishArticle;

use App\BlogContext\Domain\PublishArticle\Event\ArticlePublished;
use App\BlogContext\Domain\PublishArticle\Model\Article;
use App\BlogContext\Domain\PublishArticle\Publisher;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class PublisherTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private Publisher $publisher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);

        $this->publisher = new Publisher(
            $this->repository
        );
    }

    public function testPublishArticleWithDefaultTimestamp(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');

        $existingReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Article Title'),
            content: new Content('Article content'),
            slug: new Slug('article-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01'))
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($existingReadModel);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->callback(fn ($updateData) => ArticleStatus::PUBLISHED === $updateData->status
                && $updateData->publishedAt instanceof \DateTimeImmutable));

        // When
        $publishData = ($this->publisher)($articleId);

        // Then
        $this->assertInstanceOf(Article::class, $publishData);
        $this->assertEquals(ArticleStatus::PUBLISHED, $publishData->status);
        $this->assertInstanceOf(\DateTimeImmutable::class, $publishData->publishedAt);

        $events = $publishData->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticlePublished::class, $events[0]);
    }

    public function testPublishArticleWithSpecificTimestamp(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $publishAt = new \DateTimeImmutable('2024-02-01 10:00:00');

        $existingReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Article Title'),
            content: new Content('Article content'),
            slug: new Slug('article-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01'))
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($existingReadModel);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->callback(fn ($updateData) => ArticleStatus::PUBLISHED === $updateData->status
                && $updateData->publishedAt === $publishAt));

        // When
        $publishData = ($this->publisher)($articleId, $publishAt);

        // Then
        $this->assertEquals($publishAt, $publishData->publishedAt);
    }

    public function testPublishArticleThrowsExceptionWhenNotFound(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('update');

        // Then
        $this->expectException(\App\BlogContext\Domain\PublishArticle\Exception\ArticleNotFound::class);
        $this->expectExceptionMessage('Article with ID 550e8400-e29b-41d4-a716-446655440000 not found');

        // When
        ($this->publisher)($articleId);
    }

    public function testPublishArticleThrowsExceptionWhenAlreadyPublished(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');

        $publishedReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Article Title'),
            content: new Content('Article content'),
            slug: new Slug('article-slug'),
            status: ArticleStatus::PUBLISHED, // Already published
            authorId: 'author-123',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01')),
            publishedAt: new \DateTimeImmutable('2024-01-15')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($publishedReadModel);

        $this->repository->expects($this->never())
            ->method('update');

        // Then
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Article is already published');

        // When
        ($this->publisher)($articleId);
    }
}
