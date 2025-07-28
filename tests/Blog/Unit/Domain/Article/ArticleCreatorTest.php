<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Article;

use App\Blog\Domain\Article\ArticleCreator;
use App\Blog\Domain\Article\Shared\Exception\ArticleAlreadyExists;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Shared\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class ArticleCreatorTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $repository;

    private ArticleCreator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleWriteRepositoryInterface::class);

        $this->creator = new ArticleCreator(
            $this->repository
        );
    }

    public function testCreateArticleWithProvidedIdAndSlug(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article');
        $content = new Content('This is test content');
        $slug = new Slug('test-article');
        $authorId = new AuthorId('660e8400-e29b-41d4-a716-446655440001');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Article::class));

        // When
        $article = ($this->creator)($articleId, $title, $content, $slug, $authorId);

        // Then
        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals($articleId, $article->id());
        $this->assertEquals($title, $article->title());
        $this->assertEquals($content, $article->content());
        $this->assertEquals($slug, $article->slug());
        $this->assertEquals($authorId, $article->authorId());
    }

    public function testCreateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article');
        $content = new Content('This is test content');
        $slug = new Slug('existing-slug');
        $authorId = new AuthorId('660e8400-e29b-41d4-a716-446655440001');

        $existingArticle = Article::create(
            articleId: new ArticleId('770e8400-e29b-41d4-a716-446655440002'),
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $authorId
        );
        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($existingArticle);

        $this->repository->expects($this->never())
            ->method('add');

        // Then
        $this->expectException(ArticleAlreadyExists::class);

        // When
        ($this->creator)($articleId, $title, $content, $slug, $authorId);
    }
}
