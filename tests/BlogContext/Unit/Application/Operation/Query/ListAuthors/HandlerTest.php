<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\ListAuthors;

use App\BlogContext\Application\Operation\Query\ListAuthors\Handler;
use App\BlogContext\Application\Operation\Query\ListAuthors\Query;
use App\BlogContext\Application\Operation\Query\ListAuthors\View;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->handler = new Handler($this->repository);
    }

    public function testHandleListAuthorsSuccess(): void
    {
        // Given
        $query = new Query(
            page: 1,
            limit: 10
        );

        $author1 = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('John Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('john@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Bio 1'),
            new \DateTimeImmutable('2024-01-15')
        );

        $author2 = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('660e8400-e29b-41d4-a716-446655440001'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Bio 2'),
            new \DateTimeImmutable('2024-01-16')
        );

        $this->repository->expects($this->once())
            ->method('findAllPaginated')
            ->with(10, 0) // limit and offset
            ->willReturn([$author1, $author2]);

        $this->repository->expects($this->once())
            ->method('countAll')
            ->willReturn(2);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(View::class, $view);
        $this->assertCount(2, $view->authors);
        $this->assertEquals(2, $view->total);
        $this->assertEquals(1, $view->page);
        $this->assertEquals(10, $view->limit);

        // Check first author
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $view->authors[0]->id);
        $this->assertEquals('John Doe', $view->authors[0]->name);
        $this->assertEquals('john@example.com', $view->authors[0]->email);

        // Check second author
        $this->assertEquals('660e8400-e29b-41d4-a716-446655440001', $view->authors[1]->id);
        $this->assertEquals('Jane Doe', $view->authors[1]->name);
        $this->assertEquals('jane@example.com', $view->authors[1]->email);
    }

    public function testHandleListAuthorsEmptyResult(): void
    {
        // Given
        $query = new Query(
            page: 1,
            limit: 20
        );

        $this->repository->expects($this->once())
            ->method('findAllPaginated')
            ->with(20, 0)
            ->willReturn([]);

        $this->repository->expects($this->once())
            ->method('countAll')
            ->willReturn(0);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertCount(0, $view->authors);
        $this->assertEquals(0, $view->total);
    }

    public function testHandleListAuthorsPagination(): void
    {
        // Given - page 2 with limit 5
        $query = new Query(
            page: 2,
            limit: 5
        );

        $this->repository->expects($this->once())
            ->method('findAllPaginated')
            ->with(5, 5) // limit and offset (page 2 = offset 5)
            ->willReturn([]);

        $this->repository->expects($this->once())
            ->method('countAll')
            ->willReturn(10); // Total 10 authors

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertEquals(2, $view->page);
        $this->assertEquals(5, $view->limit);
        $this->assertEquals(10, $view->total);
    }
}
