<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Domain\Category;

use App\Blog\Domain\Category\CategoryUpdater;
use App\Blog\Domain\Category\Shared\Exception\CategoryNotFound;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryUpdaterTest extends TestCase
{
    private CategoryWriteRepositoryInterface&MockObject $categoryRepository;

    private CategoryUpdater $categoryUpdater;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryUpdater = new CategoryUpdater($this->categoryRepository);
    }

    #[Test]
    public function updateCategory_withValidData_returnsUpdatedCategory(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $originalName = new CategoryName('Technology');
        $originalSlug = new CategorySlug('technology');
        $originalDescription = new Description('Original description');
        $originalOrder = new Order(1);

        $newName = new CategoryName('Tech & Programming');
        $newSlug = new CategorySlug('tech-programming');
        $newDescription = new Description('Updated description');
        $newOrder = new Order(2);

        $existingCategory = Category::create(
            id: $categoryId,
            categoryName: $originalName,
            categorySlug: $originalSlug,
            description: $originalDescription,
            order: $originalOrder
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($newSlug->getValue()))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf(Category::class));

        // Act
        $result = ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $newName,
            slug: $newSlug,
            description: $newDescription,
            order: $newOrder
        );

        // Assert
        $this->assertInstanceOf(Category::class, $result);
        $this->assertTrue($result->getName()->equals($newName));
        $this->assertTrue($result->getSlug()->equals($newSlug));
        $this->assertTrue($result->getDescription()->equals($newDescription));
        $this->assertTrue($result->getOrder()->equals($newOrder));
    }

    #[Test]
    public function updateCategory_withParentChange_updatesParent(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $newParentId = new CategoryId('parent-456');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Description');
        $order = new Order(1);

        $existingCategory = Category::create(
            id: $categoryId,
            categoryName: $name,
            categorySlug: $slug,
            description: $description,
            order: $order
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($existingCategory);

        // Since slug is the same, should not check uniqueness
        $this->categoryRepository
            ->expects($this->never())
            ->method('existsWithSlug');

        $this->categoryRepository
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(fn (Category $category): bool => 'parent-456' === $category->getParentId()?->getValue()));

        // Act
        $result = ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $newParentId,
            order: $order
        );

        // Assert
        $this->assertTrue($result->getParentId()->equals($newParentId));
        $this->assertFalse($result->isRoot());
    }

    #[Test]
    public function updateCategory_clearParent_makesRoot(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $parentId = new CategoryId('parent-456');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Description');
        $order = new Order(1);

        $existingCategory = Category::create(
            id: $categoryId,
            categoryName: $name,
            categorySlug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($existingCategory);

        // Since slug is the same, should not check uniqueness
        $this->categoryRepository
            ->expects($this->never())
            ->method('existsWithSlug');

        $this->categoryRepository
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(fn (Category $category): bool => !$category->getParentId() instanceof CategoryId));

        // Act
        $result = ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            order: $order,
            clearParent: true
        );

        // Assert
        $this->assertNotInstanceOf(CategoryId::class, $result->getParentId());
        $this->assertTrue($result->isRoot());
    }

    #[Test]
    public function updateCategory_withSameSlug_doesNotCheckSlugUniqueness(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Description');
        $order = new Order(1);

        $existingCategory = Category::create(
            id: $categoryId,
            categoryName: $name,
            categorySlug: $slug,
            description: $description,
            order: $order
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($existingCategory);

        // Should not check slug uniqueness when slug is not changing
        $this->categoryRepository
            ->expects($this->never())
            ->method('existsWithSlug');

        $this->categoryRepository
            ->expects($this->once())
            ->method('update');

        // Act
        ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            order: $order
        );
    }

    #[Test]
    public function updateCategory_withExistingSlug_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $name = new CategoryName('Technology');
        $originalSlug = new CategorySlug('technology');
        $newSlug = new CategorySlug('programming');
        $description = new Description('Description');
        $order = new Order(1);

        $existingCategory = Category::create(
            id: $categoryId,
            categoryName: $name,
            categorySlug: $originalSlug,
            description: $description,
            order: $order
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($newSlug->getValue()))
            ->willReturn(true);

        $this->categoryRepository
            ->expects($this->never())
            ->method('add');

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Slug already exists: programming');

        ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $name,
            slug: $newSlug,
            description: $description,
            order: $order
        );
    }

    #[Test]
    public function updateCategory_categoryNotFound_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('nonexistent-123');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Description');

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willThrowException(new CategoryNotFound($categoryId));

        $this->categoryRepository
            ->expects($this->never())
            ->method('add');

        // Act & Assert
        $this->expectException(CategoryNotFound::class);

        ($this->categoryUpdater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description
        );
    }
}
