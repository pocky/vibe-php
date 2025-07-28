<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Category\UpdateCategory;

use App\Blog\Application\Operation\Command\Category\UpdateCategory\Command;
use App\Blog\Application\Operation\Command\Category\UpdateCategory\Handler;
use App\Blog\Domain\Category\CategoryUpdater;
use App\Blog\Domain\Category\Shared\Event\CategoryUpdated;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    public $categoryRepository;

    private EventBusInterface&MockObject $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $updater = $this->createMock(CategoryUpdater::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler($updater, $this->eventBus);
    }

    #[Test]
    public function validCommand_withAllFields_updatesCategory(): void
    {
        $command = new Command(
            categoryId: 'category-123',
            name: 'Updated Technology',
            slug: 'updated-technology',
            description: 'Updated technology articles',
            parentId: 'parent-456',
            order: 15
        );

        // Create existing category for the updater to retrieve
        $existingCategory = Category::create(
            id: new CategoryId('category-123'),
            categoryName: new CategoryName('Technology'),
            categorySlug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(10)
        );

        // Clear the creation event from the category
        $existingCategory->releaseEvents();

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->callback(fn (CategoryId $id): bool => 'category-123' === $id->getValue()))
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with($this->callback(fn (Slug $slug): bool => 'updated-technology' === $slug->getValue()))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Category::class));

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryUpdated::class));

        ($this->handler)($command);
    }
}
