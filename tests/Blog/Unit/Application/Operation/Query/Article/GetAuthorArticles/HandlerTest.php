<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Article\GetAuthorArticles;

use App\Blog\Application\Operation\Query\GetAuthorArticles\Handler;
use App\Blog\Application\Operation\Query\GetAuthorArticles\Query;
use App\Blog\Application\Operation\Query\GetAuthorArticles\View;
use App\Blog\Domain\Article\Shared\Repository\ArticleReadRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $authorRepository;

    private ArticleReadRepositoryInterface&MockObject $articleRepository;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->authorRepository = $this->createMock(AuthorRepositoryInterface::class);
        $this->articleRepository = $this->createMock(ArticleReadRepositoryInterface::class);
        $this->handler = new Handler($this->authorRepository, $this->articleRepository);
    }

    public function testHandleGetAuthorArticlesSuccess(): void
    {
        // Given
        $query = new Query(
            authorId: '550e8400-e29b-41d4-a716-446655440000',
            page: 1,
            limit: 10
        );

        \App\Blog\Domain\Author\CreateAuthor\Model\Author::create(
            new \App\Blog\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorName('John Doe'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorEmail('john@example.com'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorBio('Bio'),
            new \DateTimeImmutable('2024-01-15')
        );

        // Mock author exists check
        $this->authorRepository->expects($this->once())
            ->method('existsById')
            ->with($this->isInstanceOf(\App\Blog\Domain\Author\Shared\ValueObject\AuthorId::class))
            ->willReturn(true);

        // Mock articles data
        $articles = [
            [
                'id' => 'article-1',
                'title' => 'First Article',
                'slug' => 'first-article',
                'status' => 'published',
                'publishedAt' => '2024-01-20 10:00:00',
            ],
            [
                'id' => 'article-2',
                'title' => 'Second Article',
                'slug' => 'second-article',
                'status' => 'draft',
                'publishedAt' => null,
            ],
        ];

        $this->articleRepository->expects($this->once())
            ->method('findByAuthorId')
            ->with(
                $this->isInstanceOf(\App\Blog\Domain\Author\Shared\ValueObject\AuthorId::class),
                10,
                0
            )
            ->willReturn($articles);

        $this->articleRepository->expects($this->once())
            ->method('countByAuthorId')
            ->with($this->isInstanceOf(\App\Blog\Domain\Author\Shared\ValueObject\AuthorId::class))
            ->willReturn(2);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $view->authorId);
        $this->assertCount(2, $view->articles);
        $this->assertEquals(2, $view->total);
        $this->assertEquals(1, $view->page);
        $this->assertEquals(10, $view->limit);

        // Check first article
        $this->assertEquals('article-1', $view->articles[0]->id);
        $this->assertEquals('First Article', $view->articles[0]->title);
        $this->assertEquals('first-article', $view->articles[0]->slug);
        $this->assertEquals('published', $view->articles[0]->status);
        $this->assertInstanceOf(\DateTimeImmutable::class, $view->articles[0]->publishedAt);

        // Check second article
        $this->assertEquals('article-2', $view->articles[1]->id);
        $this->assertEquals('Second Article', $view->articles[1]->title);
        $this->assertEquals('second-article', $view->articles[1]->slug);
        $this->assertEquals('draft', $view->articles[1]->status);
        $this->assertNull($view->articles[1]->publishedAt);
    }

    public function testHandleGetAuthorArticlesAuthorNotFound(): void
    {
        // Given
        $query = new Query(
            authorId: '550e8400-e29b-41d4-a716-446655440000',
            page: 1,
            limit: 10
        );

        $this->authorRepository->expects($this->once())
            ->method('existsById')
            ->willReturn(false);

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Author with ID "550e8400-e29b-41d4-a716-446655440000" not found');

        // When
        ($this->handler)($query);
    }

    public function testHandleGetAuthorArticlesEmptyResult(): void
    {
        // Given
        $query = new Query(
            authorId: '550e8400-e29b-41d4-a716-446655440000',
            page: 1,
            limit: 10
        );

        $this->authorRepository->expects($this->once())
            ->method('existsById')
            ->willReturn(true);

        $this->articleRepository->expects($this->once())
            ->method('findByAuthorId')
            ->willReturn([]);

        $this->articleRepository->expects($this->once())
            ->method('countByAuthorId')
            ->willReturn(0);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertCount(0, $view->articles);
        $this->assertEquals(0, $view->total);
    }
}
