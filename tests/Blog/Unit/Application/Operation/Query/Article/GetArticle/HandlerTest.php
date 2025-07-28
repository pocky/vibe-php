<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Article\GetArticle;

use App\Blog\Application\Operation\Query\GetArticle\Handler;
use App\Blog\Application\Operation\Query\GetArticle\Query;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\GetArticle\GetterInterface;
use App\Blog\Domain\GetArticle\Model\Article;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $getter;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->getter = $this->createMock(GetterInterface::class);
        $this->handler = new Handler($this->getter);
    }

    public function testHandleGetArticleQuery(): void
    {
        // Given
        $query = new Query(id: '550e8400-e29b-41d4-a716-446655440000');

        $article = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            content: new Content('Test content'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            authorId: 'author-123',
            timestamps: Timestamps::create(),
            publishedAt: new \DateTimeImmutable()
        );

        $this->getter->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn ($id): bool => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()))
            ->willReturn($article);

        // When
        $result = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $result->id->getValue());
        $this->assertEquals('Test Article', $result->title->getValue());
        $this->assertEquals('Test content', $result->content->getValue());
        $this->assertEquals('test-article', $result->slug->getValue());
        $this->assertEquals('published', $result->status->value);
        $this->assertEquals('author-123', $result->authorId);
    }

    public function testHandleGetArticleNotFound(): void
    {
        // Given
        $query = new Query(id: '550e8400-e29b-41d4-a716-446655440000');

        $this->getter->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticleId::class))
            ->willThrowException(new \App\Blog\Domain\Article\GetArticle\Exception\ArticleNotFound(new ArticleId('550e8400-e29b-41d4-a716-446655440000')));

        // Then
        $this->expectException(\App\Blog\Domain\Article\GetArticle\Exception\ArticleNotFound::class);

        // When
        ($this->handler)($query);
    }
}
