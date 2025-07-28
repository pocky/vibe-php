<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Author\UpdateAuthor;

use App\Blog\Application\Operation\Command\Author\UpdateAuthor\Command;
use App\Blog\Application\Operation\Command\Author\UpdateAuthor\Handler;
use App\Blog\Domain\Author\AuthorUpdater;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    public $repository;

    private EventBusInterface&MockObject $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $updater = $this->createMock(AuthorUpdater::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler($updater, $this->eventBus);
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

        $existingAuthor = \App\Blog\Domain\Author\Shared\Model\Author::create(
            new \App\Blog\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorBio('Original bio')
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
            ->with($this->isInstanceOf(\App\Blog\Domain\Author\UpdateAuthor\Model\Author::class));

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

        $existingAuthor = \App\Blog\Domain\Author\Shared\Model\Author::create(
            new \App\Blog\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\Blog\Domain\Shared\ValueObject\AuthorBio('Original bio')
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
