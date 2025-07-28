<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Author;

use App\Blog\Domain\Author\AuthorDeletor;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthorDeletorTest extends TestCase
{
    private AuthorWriteRepositoryInterface&MockObject $repository;

    private AuthorDeletor $deletor;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorWriteRepositoryInterface::class);
        $this->deletor = new AuthorDeletor($this->repository);
    }

    public function testDeleteAuthorSuccess(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        $this->repository->expects($this->once())
            ->method('removeById')
            ->with($authorId);

        // When & Then - should not throw exception
        ($this->deletor)($authorId);

        // If we reach here, the method succeeded without throwing
        $this->assertTrue(true);
    }

    public function testDeleteAuthorNotFoundThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        $this->repository->expects($this->once())
            ->method('removeById')
            ->with($authorId)
            ->willThrowException(new \RuntimeException('Author not found: 550e8400-e29b-41d4-a716-446655440000'));

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Author not found: 550e8400-e29b-41d4-a716-446655440000');

        // When
        ($this->deletor)($authorId);
    }

    public function testDeleteAuthorWithArticlesThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        $this->repository->expects($this->once())
            ->method('removeById')
            ->with($authorId)
            ->willThrowException(new \RuntimeException('Cannot delete author - they have articles'));

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot delete author - they have articles');

        // When
        ($this->deletor)($authorId);
    }
}
