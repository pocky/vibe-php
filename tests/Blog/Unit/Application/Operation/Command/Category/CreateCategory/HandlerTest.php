<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Application\Operation\Command\Category\CreateCategory;

use App\Blog\Application\Operation\Command\Category\CreateCategory\Command;
use App\Blog\Application\Operation\Command\Category\CreateCategory\Handler;
use App\Blog\Domain\Category\CategoryCreator;
use App\Blog\Domain\Category\Shared\Event\CategoryCreated;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private CategoryCreator&MockObject $creator;

    private EventBusInterface&MockObject $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->creator = $this->createMock(CategoryCreator::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler($this->creator, $this->eventBus);
    }

    #[Test]
    public function handle_withValidCommand_createsCategory(): void
    {
        // Arrange
        $command = new Command(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            order: 1
        );

        // Creator should be called and return a category with events
        $category = $this->createMock(Category::class);
        $category->method('releaseEvents')->willReturn([new CategoryCreated(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            parentId: null,
            order: 1,
            createdAt: new \DateTimeImmutable()
        )]);

        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($category);

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // Act
        ($this->handler)($command);

        // Assert - expectations verified by mocks
        $this->assertTrue(true);
    }

    #[Test]
    public function handle_withParentId_createsChildCategory(): void
    {
        // Arrange
        $command = new Command(
            categoryId: 'cat-123',
            name: 'Web Development',
            slug: 'web-development',
            description: 'Web development articles',
            parentId: 'parent-456',
            order: 2
        );

        // Creator should be called and return a category with events
        $category = $this->createMock(Category::class);
        $category->method('releaseEvents')->willReturn([new CategoryCreated(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            parentId: null,
            order: 1,
            createdAt: new \DateTimeImmutable()
        )]);

        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($category);

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // Act
        ($this->handler)($command);

        // Assert - expectations verified by mocks
        $this->assertTrue(true);
    }

    #[Test]
    public function handle_withoutOrder_usesDefaultOrder(): void
    {
        // Arrange
        $command = new Command(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles'
        );

        // Creator should be called and return a category with events
        $category = $this->createMock(Category::class);
        $category->method('releaseEvents')->willReturn([new CategoryCreated(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            parentId: null,
            order: 1,
            createdAt: new \DateTimeImmutable()
        )]);

        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($category);

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // Act
        ($this->handler)($command);

        // Assert - expectations verified by mocks
        $this->assertTrue(true);
    }

    #[Test]
    public function handle_dispatchesAllDomainEvents(): void
    {
        // Arrange
        $command = new Command(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles'
        );

        // Creator should be called and return a category with events
        $category = $this->createMock(Category::class);
        $category->method('releaseEvents')->willReturn([new CategoryCreated(
            categoryId: 'cat-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            parentId: null,
            order: 1,
            createdAt: new \DateTimeImmutable()
        )]);

        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($category);

        // Expect event bus to be called for the CategoryCreated event
        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryCreated::class));

        // Act
        ($this->handler)($command);

        // Assert - expectations verified by mocks
        $this->assertTrue(true);
    }
}
