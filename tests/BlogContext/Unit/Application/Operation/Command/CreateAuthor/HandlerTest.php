<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\CreateAuthor;

use App\BlogContext\Application\Operation\Command\CreateAuthor\Command;
use App\BlogContext\Application\Operation\Command\CreateAuthor\Handler;
use App\BlogContext\Domain\CreateAuthor\Creator;
use App\BlogContext\Domain\CreateAuthor\Event\AuthorCreated;
use App\BlogContext\Domain\Shared\Generator\AuthorIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private AuthorIdGeneratorInterface&MockObject $idGenerator;
    private EventBusInterface&MockObject $eventBus;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->idGenerator = $this->createMock(AuthorIdGeneratorInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        // Configure idGenerator to return a real AuthorId instance
        $this->idGenerator->method('nextIdentity')
            ->willReturnCallback(fn () => new \App\BlogContext\Domain\Shared\ValueObject\AuthorId('550e8400-e29b-41d4-a716-446655440000'));

        // Use real Creator with mocked dependencies
        $creator = new Creator($this->repository, $this->idGenerator);

        $this->handler = new Handler($creator, $this->repository, $this->eventBus);
    }

    public function testHandleCreateAuthorSuccess(): void
    {
        // Given
        $command = new Command(
            name: 'John Doe',
            email: 'john@example.com',
            bio: 'A passionate writer'
        );

        // Repository expectations
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null); // Email not in use

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(\App\BlogContext\Domain\CreateAuthor\Model\Author::class));

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(AuthorCreated::class));

        // When
        ($this->handler)($command);
    }

    public function testHandleCreateAuthorWithEmptyBio(): void
    {
        // Given
        $command = new Command(
            name: 'Jane Smith',
            email: 'jane@example.com',
            bio: '' // Empty bio
        );

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(\App\BlogContext\Domain\CreateAuthor\Model\Author::class));

        $this->eventBus->expects($this->once())
            ->method('__invoke');

        // When
        ($this->handler)($command);
    }
}
