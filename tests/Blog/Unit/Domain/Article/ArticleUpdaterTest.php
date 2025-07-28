<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Article;

use App\Blog\Domain\Article\ArticleUpdater;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class ArticleUpdaterTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $repository;

    private ArticleUpdater $updater;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleWriteRepositoryInterface::class);

        $this->updater = new ArticleUpdater(
            $this->repository
        );
    }

    public function testUpdateArticle(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Updated Title');
        $content = new Content('Updated content');
        $slug = new Slug('updated-slug');

        $article = $this->createMock(Article::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $article->expects($this->once())
            ->method('slug')
            ->willReturn($slug);

        $article->expects($this->once())
            ->method('update')
            ->with($title, $content, $slug);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($article);

        // When
        $result = ($this->updater)($articleId, $title, $content, $slug);

        // Then
        $this->assertSame($article, $result);
    }

    public function testUpdateArticleThrowsExceptionWhenNotFound(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Updated Title');
        $content = new Content('Updated content');
        $slug = new Slug('updated-slug');

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('update');

        // Then
        $this->expectException(\RuntimeException::class);

        // When
        ($this->updater)($articleId, $title, $content, $slug);
    }

    public function testUpdateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Updated Title');
        $content = new Content('Updated content');
        $newSlug = new Slug('existing-slug');
        $oldSlug = new Slug('old-slug');

        $article = $this->createMock(Article::class);
        $existingArticle = $this->createMock(Article::class);
        $existingArticleId = new ArticleId('different-id');

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $article->expects($this->once())
            ->method('slug')
            ->willReturn($oldSlug);

        // oldSlug is a real Slug object, not a mock
        // The slug comparison will happen naturally

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($newSlug)
            ->willReturn($existingArticle);

        $existingArticle->expects($this->once())
            ->method('id')
            ->willReturn($existingArticleId);

        $existingArticleId->expects($this->once())
            ->method('equals')
            ->with($articleId)
            ->willReturn(false);

        $newSlug->expects($this->once())
            ->method('getValue')
            ->willReturn('existing-slug');

        // Then
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Slug already exists: existing-slug');

        // When
        ($this->updater)($articleId, $title, $content, $newSlug);
    }
}
