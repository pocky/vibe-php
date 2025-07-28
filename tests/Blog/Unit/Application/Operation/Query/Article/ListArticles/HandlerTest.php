<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Article\ListArticles;

use App\Blog\Application\Operation\Query\ListArticles\Handler;
use App\Blog\Application\Operation\Query\ListArticles\Query;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\GetArticles\ArticlesListData;
use App\Blog\Domain\GetArticles\ListCriteria;
use App\Blog\Domain\GetArticles\ListerInterface;
use App\Blog\Domain\GetArticles\Model\Article;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $lister;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->lister = $this->createMock(ListerInterface::class);
        $this->handler = new Handler($this->lister);
    }

    public function testHandleListArticlesQuery(): void
    {
        // Given
        $query = new Query(
            page: 1,
            limit: 10,
            sortBy: null,
            sortOrder: 'asc',
            status: 'published',
            authorId: null
        );

        $article1 = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440001'),
            title: new Title('First Article'),
            slug: new Slug('first-article'),
            status: ArticleStatus::PUBLISHED,
            authorId: 'author-123',
            timestamps: Timestamps::create(),
            publishedAt: new \DateTimeImmutable(),
            excerpt: 'First content excerpt'
        );

        $article2 = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440002'),
            title: new Title('Second Article'),
            slug: new Slug('second-article'),
            status: ArticleStatus::PUBLISHED,
            authorId: 'author-123',
            timestamps: Timestamps::create(),
            publishedAt: new \DateTimeImmutable(),
            excerpt: 'Second content excerpt'
        );

        $articlesListData = ArticlesListData::create(
            articles: [$article1, $article2],
            total: 2,
            page: 1,
            limit: 10
        );

        $this->lister->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (ListCriteria $listCriteria): bool => ArticleStatus::PUBLISHED === $listCriteria->status
                && null === $listCriteria->authorId
                && 1 === $listCriteria->page
                && 10 === $listCriteria->limit
                && 'createdAt' === $listCriteria->sortBy
                && 'ASC' === $listCriteria->sortOrder))
            ->willReturn($articlesListData);

        // When
        $result = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(ArticlesListData::class, $result);
        $this->assertCount(2, $result->articles);
        $this->assertEquals(2, $result->total);
        $this->assertEquals(1, $result->page);
        $this->assertEquals(10, $result->limit);
        $this->assertEquals(1, $result->totalPages);

        // Check first article
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $result->articles[0]->id->getValue());
        $this->assertEquals('First Article', $result->articles[0]->title->getValue());
    }

    public function testHandleEmptyListQuery(): void
    {
        // Given
        $query = new Query();

        $articlesListData = ArticlesListData::create(
            articles: [],
            total: 0,
            page: 1,
            limit: 20
        );

        $this->lister->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (ListCriteria $listCriteria): bool => !$listCriteria->status instanceof ArticleStatus
                && null === $listCriteria->authorId
                && 1 === $listCriteria->page
                && 20 === $listCriteria->limit))
            ->willReturn($articlesListData);

        // When
        $result = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(ArticlesListData::class, $result);
        $this->assertCount(0, $result->articles);
        $this->assertEquals(0, $result->total);
    }
}
