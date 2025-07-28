<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Domain\Category;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\CategoryGetter;
use App\Blog\Domain\Category\Shared\Exception\CategoryNotFound;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryGetterTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private CategoryGetter $categoryGetter;

    protected function setUp(): void
    {
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->categoryGetter = new CategoryGetter($this->categoryReadRepository);
    }

    #[Test]
    public function getCategory_withExistingId_returnsCategoryReadModel(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $categoryReadModel = new CategoryReadModel(
            id: $categoryId,
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($categoryReadModel);

        // Act
        $result = ($this->categoryGetter)($categoryId);

        // Assert
        $this->assertSame($categoryReadModel, $result);
        $this->assertInstanceOf(CategoryReadModel::class, $result);
        $this->assertTrue($result->id->equals($categoryId));
        $this->assertSame('Technology', $result->name->getValue());
        $this->assertSame('technology', $result->slug->getValue());
        $this->assertSame('Technology articles', $result->description->getValue());
        $this->assertNotInstanceOf(CategoryId::class, $result->parentId);
        $this->assertSame(1, $result->order->getValue());
        $this->assertTrue($result->isRoot());
    }

    #[Test]
    public function getCategory_withExistingIdAndParent_returnsCategoryReadModel(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $parentId = new CategoryId('parent-456');
        $categoryReadModel = new CategoryReadModel(
            id: $categoryId,
            name: new CategoryName('Web Development'),
            slug: new CategorySlug('web-development'),
            description: new Description('Web development articles'),
            parentId: $parentId,
            order: new Order(2),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 11:00:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($categoryReadModel);

        // Act
        $result = ($this->categoryGetter)($categoryId);

        // Assert
        $this->assertSame($categoryReadModel, $result);
        $this->assertTrue($result->parentId->equals($parentId));
        $this->assertFalse($result->isRoot());
        $this->assertTrue($result->hasParent());
    }

    #[Test]
    public function getCategory_withNonExistentId_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('nonexistent-123');

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(CategoryNotFound::class);
        ($this->categoryGetter)($categoryId);
    }

    #[Test]
    public function getCategory_bySlug_returnsCategoryReadModel(): void
    {
        // Arrange
        $categorySlug = new CategorySlug('technology');
        $categoryReadModel = new CategoryReadModel(
            id: new CategoryId('cat-123'),
            name: new CategoryName('Technology'),
            slug: $categorySlug,
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findBySlug')
            ->with($categorySlug)
            ->willReturn($categoryReadModel);

        // Act
        $result = ($this->categoryGetter)($categorySlug);

        // Assert
        $this->assertSame($categoryReadModel, $result);
        $this->assertTrue($result->slug->equals($categorySlug));
    }

    #[Test]
    public function getCategory_byNonExistentSlug_throwsException(): void
    {
        // Arrange
        $categorySlug = new CategorySlug('nonexistent');

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findBySlug')
            ->with($categorySlug)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(CategoryNotFound::class);
        ($this->categoryGetter)($categorySlug);
    }
}
