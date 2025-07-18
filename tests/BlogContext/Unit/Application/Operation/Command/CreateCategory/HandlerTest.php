<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\CreateCategory;

use App\BlogContext\Application\Operation\Command\CreateCategory\Command;
use App\BlogContext\Application\Operation\Command\CreateCategory\Handler;
use App\BlogContext\Domain\CreateCategory\CreatorInterface;
use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\CreateCategory\Event\CategoryCreated;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private Handler $handler;
    private CreatorInterface&MockObject $creator;
    private EventBusInterface&MockObject $eventBus;

    protected function setUp(): void
    {
        $this->creator = $this->createMock(CreatorInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->handler = new Handler($this->creator, $this->eventBus);
    }

    public function testHandleCreatesCategoryAndDispatchesEvent(): void
    {
        // Given
        $command = new Command(
            categoryId: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Technology',
            slug: 'technology',
            parentId: null,
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00')
        );

        // Create a real category for the test
        $expectedCategory = new Category(
            id: new CategoryId('550e8400-e29b-41d4-a716-446655440000'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            path: new CategoryPath('technology'),
            parentId: null,
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01 12:00:00')
        );

        // Configure mocks
        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->isInstanceOf(CategoryId::class),
                $this->isInstanceOf(CategoryName::class),
                $this->isInstanceOf(CategorySlug::class),
                null,
                $this->isInstanceOf(\DateTimeImmutable::class)
            )
            ->willReturn($expectedCategory);

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // When
        $this->handler->__invoke($command);

        // Then - assertions are implicit in the mock expectations
    }

    public function testHandleWithParentCategory(): void
    {
        // Given
        $command = new Command(
            categoryId: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Web Development',
            slug: 'web-development',
            parentId: '550e8400-e29b-41d4-a716-446655440000',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00')
        );

        // Create a real category for the test
        $expectedCategory = new Category(
            id: new CategoryId('550e8400-e29b-41d4-a716-446655440001'),
            name: new CategoryName('Web Development'),
            slug: new CategorySlug('web-development'),
            path: new CategoryPath('technology/web-development'),
            parentId: new CategoryId('550e8400-e29b-41d4-a716-446655440000'),
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01 12:00:00')
        );

        // Configure mocks
        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->isInstanceOf(CategoryId::class),
                $this->isInstanceOf(CategoryName::class),
                $this->isInstanceOf(CategorySlug::class),
                $this->isInstanceOf(CategoryId::class),
                $this->isInstanceOf(\DateTimeImmutable::class)
            )
            ->willReturn($expectedCategory);

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // When
        $this->handler->__invoke($command);

        // Then - assertions are implicit in the mock expectations
    }
}
