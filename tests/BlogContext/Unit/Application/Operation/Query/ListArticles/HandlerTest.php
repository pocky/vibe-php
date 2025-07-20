<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\ListArticles;

use App\BlogContext\Application\Operation\Query\ListArticles\Handler;
use App\BlogContext\Application\Operation\Query\ListArticles\Query;
use App\BlogContext\Domain\GetArticles\ArticlesListData;
use App\BlogContext\Domain\GetArticles\ListCriteria;
use App\BlogContext\Domain\GetArticles\ListerInterface;
use App\BlogContext\Domain\GetArticles\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private ListerInterface $lister;
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
            ->with($this->callback(fn (ListCriteria $criteria) => ArticleStatus::PUBLISHED === $criteria->status
                && null === $criteria->authorId
                && 1 === $criteria->page
                && 10 === $criteria->limit
                && 'createdAt' === $criteria->sortBy
                && 'ASC' === $criteria->sortOrder))
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
            ->with($this->callback(fn (ListCriteria $criteria) => !$criteria->status instanceof ArticleStatus
                && null === $criteria->authorId
                && 1 === $criteria->page
                && 20 === $criteria->limit))
            ->willReturn($articlesListData);

        // When
        $result = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(ArticlesListData::class, $result);
        $this->assertCount(0, $result->articles);
        $this->assertEquals(0, $result->total);
    }
}
