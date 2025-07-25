<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\UpdateAuthor;

use App\BlogContext\Application\Operation\Command\UpdateAuthor\Command;
use App\BlogContext\Application\Operation\Command\UpdateAuthor\Handler;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\UpdateAuthor\Event\AuthorUpdated;
use App\BlogContext\Domain\UpdateAuthor\Updater;
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

        // Use real Updater with mocked repository
        $updater = new Updater($this->repository);

        $this->handler = new Handler($updater, $this->repository, $this->eventBus);
    }

    public function testHandleUpdateAuthorSuccess(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Jane Updated',
            email: 'jane.updated@example.com',
            bio: 'Updated bio'
        );

        $existingAuthor = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Original bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        // Repository expectations
        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null); // New email not in use

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf(\App\BlogContext\Domain\UpdateAuthor\Model\Author::class));

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(AuthorUpdated::class));

        // When
        ($this->handler)($command);
    }

    public function testHandleUpdateAuthorKeepingSameEmail(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Jane Updated',
            email: 'jane@example.com', // Same email
            bio: 'Updated bio'
        );

        $existingAuthor = \App\BlogContext\Domain\CreateAuthor\Model\Author::create(
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\BlogContext\Domain\Shared\ValueObject\AuthorBio('Original bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingAuthor); // Same author

        $this->repository->expects($this->once())
            ->method('update');

        $this->eventBus->expects($this->once())
            ->method('__invoke');

        // When
        ($this->handler)($command);
    }
}
