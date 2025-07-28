<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Article;

use App\Blog\Domain\Article\ArticlePublisher;
use App\Blog\Domain\Article\Shared\Exception\ArticleNotFound;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ArticlePublisherTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $repository;

    private ArticlePublisher $publisher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleWriteRepositoryInterface::class);

        $this->publisher = new ArticlePublisher(
            $this->repository
        );
    }

    public function testPublishArticle(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $article = $this->createMock(Article::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $article->expects($this->once())
            ->method('publish');

        $this->repository->expects($this->once())
            ->method('update')
            ->with($article);

        // When
        $result = ($this->publisher)($articleId);

        // Then
        $this->assertSame($article, $result);
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
        $this->expectException(ArticleNotFound::class);

        // When
        ($this->publisher)($articleId);
    }
}
