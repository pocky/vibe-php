<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\DeleteAuthor;

use App\BlogContext\Application\Operation\Command\DeleteAuthor\Command;
use App\BlogContext\Application\Operation\Command\DeleteAuthor\Handler;
use App\BlogContext\Domain\DeleteAuthor\Deletor;
use App\BlogContext\Domain\DeleteAuthor\Event\AuthorDeleted;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private EventBusInterface&MockObject $eventBus;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        // Use real Deletor with mocked repository
        $deletor = new Deletor($this->repository);

        $this->handler = new Handler($deletor, $this->repository, $this->eventBus);
    }

    public function testHandleDeleteAuthorSuccess(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $existingAuthor = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        // Repository expectations
        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->willReturn(0); // No articles

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(\App\BlogContext\Domain\DeleteAuthor\Model\Author::class));

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(AuthorDeleted::class));

        // When
        ($this->handler)($command);
    }

    public function testHandleDeleteAuthorWithArticlesFails(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $existingAuthor = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->willReturn(3); // Has 3 articles

        $this->repository->expects($this->never())
            ->method('remove');

        $this->eventBus->expects($this->never())
            ->method('__invoke');

        // Then
        $this->expectException(\App\BlogContext\Domain\DeleteAuthor\Exception\AuthorHasArticles::class);
        $this->expectExceptionMessage('Cannot delete author with ID "550e8400-e29b-41d4-a716-446655440000" because they have 3 articles.');

        // When
        ($this->handler)($command);
    }
}
