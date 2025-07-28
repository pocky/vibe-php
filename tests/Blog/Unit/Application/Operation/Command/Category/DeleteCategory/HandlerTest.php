<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Category\DeleteCategory;

use App\Blog\Application\Operation\Command\Category\DeleteCategory\Command;
use App\Blog\Application\Operation\Command\Category\DeleteCategory\Handler;
use App\Blog\Domain\Category\CategoryDeleter;
use App\Blog\Domain\Category\Shared\Event\CategoryDeleted;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\Repository\CategoryRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private EventBusInterface&MockObject $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        // Use real CategoryDeleter with mocked repositories
        $categoryDeleter = new CategoryDeleter($this->categoryRepository, $this->categoryReadRepository);

        $this->handler = new Handler($categoryDeleter, $this->eventBus);
    }

    #[Test]
    public function validCommand_deletesCategory(): void
    {
        $command = new Command(categoryId: 'category-123');

        // Create existing category for the deleter to retrieve
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

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findByParentId')
            ->with($this->callback(fn (CategoryId $id): bool => 'category-123' === $id->getValue()))
            ->willReturn([]); // No children

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('countArticlesByCategory')
            ->with($this->callback(fn (CategoryId $id): bool => 'category-123' === $id->getValue()))
            ->willReturn(0); // No articles

        $this->categoryRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Category::class));

        $this->eventBus
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(CategoryDeleted::class));

        ($this->handler)($command);
    }
}
