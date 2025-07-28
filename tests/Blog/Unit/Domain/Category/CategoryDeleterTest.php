<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Domain\Category;

use App\Blog\Domain\Category\CategoryDeleter;
use App\Blog\Domain\Category\Shared\Exception\CategoryHasArticles;
use App\Blog\Domain\Category\Shared\Exception\CategoryHasChildren;
use App\Blog\Domain\Category\Shared\Exception\CategoryNotFound;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryDeleterTest extends TestCase
{
    private CategoryWriteRepositoryInterface&MockObject $categoryRepository;

    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private CategoryDeleter $categoryDeleter;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->categoryDeleter = new CategoryDeleter(
            $this->categoryRepository,
            $this->categoryReadRepository
        );
    }

    #[Test]
    public function deleteCategory_withValidCategoryAndNoDependencies_deletesSuccessfully(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $category = Category::create(
            id: $categoryId,
            categoryName: new CategoryName('Technology'),
            categorySlug: new CategorySlug('technology'),
            description: new Description('Technology articles')
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($category);

        // Count children (0 = no children)
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findByParentId')
            ->with($categoryId)
            ->willReturn([]);

        // Count articles (0 = no articles)
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('countArticlesByCategory')
            ->with($categoryId)
            ->willReturn(0);

        $this->categoryRepository
            ->expects($this->once())
            ->method('remove')
            ->with($category);

        // Act
        ($this->categoryDeleter)($categoryId);

        // Assert - No exception should be thrown
        $this->assertTrue(true);
    }

    #[Test]
    public function deleteCategory_withChildren_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $category = Category::create(
            id: $categoryId,
            categoryName: new CategoryName('Technology'),
            categorySlug: new CategorySlug('technology'),
            description: new Description('Technology articles')
        );

        $childCategory = Category::create(
            id: new CategoryId('child-456'),
            categoryName: new CategoryName('Web Development'),
            categorySlug: new CategorySlug('web-development'),
            description: new Description('Web development articles'),
            parentId: $categoryId
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($category);

        // Mock children count (1 child)
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findByParentId')
            ->with($categoryId)
            ->willReturn([$childCategory]);

        $this->categoryReadRepository
            ->expects($this->never())
            ->method('countArticlesByCategory');

        $this->categoryRepository
            ->expects($this->never())
            ->method('remove');

        // Act & Assert
        $this->expectException(CategoryHasChildren::class);
        ($this->categoryDeleter)($categoryId);
    }

    #[Test]
    public function deleteCategory_withArticles_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $category = Category::create(
            id: $categoryId,
            categoryName: new CategoryName('Technology'),
            categorySlug: new CategorySlug('technology'),
            description: new Description('Technology articles')
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($category);

        // No children
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findByParentId')
            ->with($categoryId)
            ->willReturn([]);

        // Has articles (5 articles)
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('countArticlesByCategory')
            ->with($categoryId)
            ->willReturn(5);

        $this->categoryRepository
            ->expects($this->never())
            ->method('remove');

        // Act & Assert
        $this->expectException(CategoryHasArticles::class);
        ($this->categoryDeleter)($categoryId);
    }

    #[Test]
    public function deleteCategory_withChildrenAndArticles_throwsChildrenExceptionFirst(): void
    {
        // Arrange
        $categoryId = new CategoryId('cat-123');
        $category = Category::create(
            id: $categoryId,
            categoryName: new CategoryName('Technology'),
            categorySlug: new CategorySlug('technology'),
            description: new Description('Technology articles')
        );

        $childCategory = Category::create(
            id: new CategoryId('child-456'),
            categoryName: new CategoryName('Web Development'),
            categorySlug: new CategorySlug('web-development'),
            description: new Description('Web development articles'),
            parentId: $categoryId
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willReturn($category);

        // Has children - should check this first
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findByParentId')
            ->with($categoryId)
            ->willReturn([$childCategory]);

        // Should not check articles since children check fails first
        $this->categoryReadRepository
            ->expects($this->never())
            ->method('countArticlesByCategory');

        $this->categoryRepository
            ->expects($this->never())
            ->method('remove');

        // Act & Assert - should throw children exception, not articles
        $this->expectException(CategoryHasChildren::class);
        ($this->categoryDeleter)($categoryId);
    }

    #[Test]
    public function deleteCategory_categoryNotFound_throwsException(): void
    {
        // Arrange
        $categoryId = new CategoryId('nonexistent-123');

        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willThrowException(new CategoryNotFound($categoryId));

        $this->categoryReadRepository
            ->expects($this->never())
            ->method('findByParentId');

        $this->categoryReadRepository
            ->expects($this->never())
            ->method('countArticlesByCategory');

        $this->categoryRepository
            ->expects($this->never())
            ->method('remove');

        // Act & Assert
        $this->expectException(CategoryNotFound::class);
        ($this->categoryDeleter)($categoryId);
    }
}
