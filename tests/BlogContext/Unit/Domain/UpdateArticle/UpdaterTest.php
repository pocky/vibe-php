<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\UpdateArticle\Event\ArticleUpdated;
use App\BlogContext\Domain\UpdateArticle\Model\Article;
use App\BlogContext\Domain\UpdateArticle\Updater;
use PHPUnit\Framework\TestCase;

final class UpdaterTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private Updater $updater;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);

        $this->updater = new Updater(
            $this->repository
        );
    }

    public function testUpdateArticleWithAllFields(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $newTitle = new Title('Updated Title');
        $newContent = new Content('Updated content');
        $newSlug = new Slug('updated-slug');

        $existingReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Original Title'),
            content: new Content('Original content'),
            slug: new Slug('original-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01'))
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($existingReadModel);

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($newSlug)
            ->willReturn(null); // Slug is available

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf(Article::class));

        // When
        $updateData = ($this->updater)($articleId, $newTitle, $newContent, $newSlug);

        // Then
        $this->assertInstanceOf(Article::class, $updateData);
        $this->assertEquals($newTitle, $updateData->title);
        $this->assertEquals($newContent, $updateData->content);
        $this->assertEquals($newSlug, $updateData->slug);
        $this->assertTrue($updateData->hasChanges());

        $events = $updateData->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleUpdated::class, $events[0]);
    }

    public function testUpdateArticleWithTitleOnly(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $newTitle = new Title('Updated Title');

        $existingReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Original Title'),
            content: new Content('Original content'),
            slug: new Slug('original-slug'),
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
            ->with($this->isInstanceOf(Article::class));

        // When - only title provided
        $updateData = ($this->updater)($articleId, $newTitle);

        // Then
        $this->assertEquals($newTitle, $updateData->title);
        $this->assertEquals($existingReadModel->content, $updateData->content); // Should keep original
        $this->assertEquals($existingReadModel->slug, $updateData->slug); // Should keep original
    }

    public function testUpdateArticleThrowsExceptionWhenNotFound(): void
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
        $this->expectException(\App\BlogContext\Domain\UpdateArticle\Exception\ArticleNotFound::class);
        $this->expectExceptionMessage('Article with ID 550e8400-e29b-41d4-a716-446655440000 not found');

        // When
        ($this->updater)($articleId, new Title('New Title'));
    }

    public function testUpdateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $conflictingSlug = new Slug('existing-slug');

        $existingReadModel = new ArticleReadModel(
            id: $articleId,
            title: new Title('Original Title'),
            content: new Content('Original content'),
            slug: new Slug('original-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01'))
        );

        $conflictingReadModel = new ArticleReadModel(
            id: new ArticleId('650e8400-e29b-41d4-a716-446655440001'),
            title: new Title('Another Title'),
            content: new Content('Another content'),
            slug: $conflictingSlug,
            status: ArticleStatus::DRAFT,
            authorId: 'author-456',
            timestamps: new Timestamps(new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-01'))
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($existingReadModel);

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($conflictingSlug)
            ->willReturn($conflictingReadModel);

        $this->repository->expects($this->never())
            ->method('update');

        // Then
        $this->expectException(\App\BlogContext\Domain\UpdateArticle\Exception\SlugAlreadyExists::class);
        $this->expectExceptionMessage('Article with slug existing-slug already exists');

        // When
        ($this->updater)($articleId, null, null, $conflictingSlug);
    }
}
