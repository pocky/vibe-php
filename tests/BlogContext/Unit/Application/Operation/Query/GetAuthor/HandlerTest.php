<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\GetAuthor;

use App\BlogContext\Application\Operation\Query\GetAuthor\Handler;
use App\BlogContext\Application\Operation\Query\GetAuthor\Query;
use App\BlogContext\Application\Operation\Query\GetAuthor\View;
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

    public function testHandleGetAuthorSuccess(): void
    {
        // Given
        $query = new Query(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $author = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('John Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('john@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('A passionate writer'),
            new \DateTimeImmutable('2024-01-15 10:00:00')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($this->isInstanceOf(\App\BlogContext\Domain\Shared\ValueObject\AuthorId::class))
            ->willReturn($author);

        // When
        $view = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $view->id);
        $this->assertEquals('John Doe', $view->name);
        $this->assertEquals('john@example.com', $view->email);
        $this->assertEquals('A passionate writer', $view->bio);
        $this->assertEquals('2024-01-15 10:00:00', $view->createdAt->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-15 10:00:00', $view->updatedAt->format('Y-m-d H:i:s'));
    }

    public function testHandleGetAuthorNotFound(): void
    {
        // Given
        $query = new Query(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Author with ID "550e8400-e29b-41d4-a716-446655440000" not found');

        // When
        ($this->handler)($query);
    }
}
