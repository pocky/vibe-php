<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\ListArticles;

use App\BlogContext\Application\Operation\Query\ListArticles\Handler;
use App\BlogContext\Application\Operation\Query\ListArticles\Query;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private ArticleRepositoryInterface&MockObject $repository;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new Handler($this->repository);
    }

    public function testHandleWithArticles(): void
    {
        $query = new Query(page: 1, limit: 10, status: 'published');

        $mockPaginator = $this->createMock(PaginatorInterface::class);
        $mockPaginator->method('getItems')->willReturn([
            [
                'id' => $this->generateArticleId()->getValue(),
                'title' => 'Article 1',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'title' => 'Article 2',
            ],
        ]);
        $mockPaginator->method('getTotalItems')->willReturn(25);
        $mockPaginator->method('getCurrentPage')->willReturn(1);
        $mockPaginator->method('getItemsPerPage')->willReturn(10);
        $mockPaginator->method('hasNextPage')->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('findAllPaginated')
            ->with(1, 10, [
                'status' => 'published',
            ])
            ->willReturn($mockPaginator);

        $result = $this->handler->__invoke($query);

        $this->assertIsArray($result);
        $this->assertCount(2, $result['articles']);
        $this->assertEquals(25, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertTrue($result['hasNextPage']);
    }

    public function testHandleWithEmptyResult(): void
    {
        $query = new Query();

        $mockPaginator = $this->createMock(PaginatorInterface::class);
        $mockPaginator->method('getItems')->willReturn([]);
        $mockPaginator->method('getTotalItems')->willReturn(0);
        $mockPaginator->method('getCurrentPage')->willReturn(1);
        $mockPaginator->method('getItemsPerPage')->willReturn(20);
        $mockPaginator->method('hasNextPage')->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findAllPaginated')
            ->with(1, 20, [])
            ->willReturn($mockPaginator);

        $result = $this->handler->__invoke($query);

        $this->assertIsArray($result);
        $this->assertEmpty($result['articles']);
        $this->assertEquals(0, $result['total']);
        $this->assertFalse($result['hasNextPage']);
    }
}
