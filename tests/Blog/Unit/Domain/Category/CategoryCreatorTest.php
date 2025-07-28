<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Domain\Category;

use App\Blog\Domain\Category\CategoryCreator;
use App\Blog\Domain\Category\Shared\Exception\CategoryAlreadyExists;
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

final class CategoryCreatorTest extends TestCase
{
    private CategoryWriteRepositoryInterface&MockObject $categoryRepository;

    private CategoryCreator $categoryCreator;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryCreator = new CategoryCreator($this->categoryRepository);
    }

    #[Test]
    public function createCategory_withValidData_returnsCategory(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Technology related articles');
        $order = new Order(1);

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($slug->getValue()))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Category::class));

        // Act
        $result = ($this->categoryCreator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            order: $order
        );

        // Assert
        $this->assertInstanceOf(Category::class, $result);
        $this->assertTrue($result->getId()->equals($categoryId));
        $this->assertTrue($result->getName()->equals($name));
        $this->assertTrue($result->getSlug()->equals($slug));
        $this->assertTrue($result->getDescription()->equals($description));
        $this->assertTrue($result->getOrder()->equals($order));
        $this->assertNotInstanceOf(CategoryId::class, $result->getParentId());
        $this->assertTrue($result->isRoot());
    }

    #[Test]
    public function createCategory_withParent_returnsCategory(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $parentId = new CategoryId('parent-456');
        $name = new CategoryName('Web Development');
        $slug = new CategorySlug('web-development');
        $description = new Description('Web development articles');
        $order = new Order(2);

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($slug->getValue()))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Category::class));

        // Act
        $result = ($this->categoryCreator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order
        );

        // Assert
        $this->assertInstanceOf(Category::class, $result);
        $this->assertTrue($result->getParentId()->equals($parentId));
        $this->assertFalse($result->isRoot());
        $this->assertTrue($result->hasParent());
    }

    #[Test]
    public function createCategory_withExistingSlug_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Technology related articles');

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($slug->getValue()))
            ->willReturn(true);

        $this->categoryRepository
            ->expects($this->never())
            ->method('add');

        // Act & Assert
        $this->expectException(CategoryAlreadyExists::class);
        ($this->categoryCreator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description
        );
    }

    #[Test]
    public function createCategory_withoutOrder_usesDefaultOrder(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $name = new CategoryName('Technology');
        $slug = new CategorySlug('technology');
        $description = new Description('Technology related articles');

        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->with(new Slug($slug->getValue()))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(fn (Category $category): bool => $category->getOrder()->equals(new Order(0))));

        // Act
        $result = ($this->categoryCreator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description
        );

        // Assert
        $this->assertTrue($result->getOrder()->equals(new Order(0)));
    }
}
