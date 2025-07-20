<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\Creator;
use App\BlogContext\Domain\CreateArticle\Event\ArticleCreated;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\CreateArticle\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class CreatorTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private Creator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);

        $this->creator = new Creator(
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
        $authorId = 'author-123';

        $this->repository->expects($this->once())
            ->method('existsWithSlug')
            ->with($slug)
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Article::class));

        // When
        $articleData = ($this->creator)($articleId, $title, $content, $slug, $authorId);

        // Then
        $this->assertInstanceOf(Article::class, $articleData);
        $this->assertEquals($articleId, $articleData->id);
        $this->assertEquals($title, $articleData->title);
        $this->assertEquals($content, $articleData->content);
        $this->assertEquals($slug, $articleData->slug);
        $this->assertEquals($authorId, $articleData->authorId);

        $events = $articleData->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleCreated::class, $events[0]);
    }

    public function testCreateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article');
        $content = new Content('This is test content');
        $slug = new Slug('existing-slug');
        $authorId = 'author-123';

        $this->repository->expects($this->once())
            ->method('existsWithSlug')
            ->with($slug)
            ->willReturn(true);

        $this->repository->expects($this->never())
            ->method('add');

        // Then
        $this->expectException(ArticleAlreadyExists::class);

        // When
        ($this->creator)($articleId, $title, $content, $slug, $authorId);
    }
}
