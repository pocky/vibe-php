<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\GetArticle;

use App\BlogContext\Application\Operation\Query\GetArticle\Handler;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\BlogContext\Domain\GetArticle\GetterInterface;
use App\BlogContext\Domain\GetArticle\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private GetterInterface $getter;
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
            ->with($this->callback(fn ($id) => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()))
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
            ->willThrowException(new \App\BlogContext\Domain\GetArticle\Exception\ArticleNotFound(new ArticleId('550e8400-e29b-41d4-a716-446655440000')));

        // Then
        $this->expectException(\App\BlogContext\Domain\GetArticle\Exception\ArticleNotFound::class);

        // When
        ($this->handler)($query);
    }
}
