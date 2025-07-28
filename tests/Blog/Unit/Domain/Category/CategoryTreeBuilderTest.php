<?php

declare(strict_types=1);

namespace Tests\Blog\Unit\Domain\Category;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\CategoryTreeBuilder;
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

final class CategoryTreeBuilderTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private CategoryTreeBuilder $categoryTreeBuilder;

    protected function setUp(): void
    {
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->categoryTreeBuilder = new CategoryTreeBuilder($this->categoryReadRepository);
    }

    #[Test]
    public function buildTree_withNoCategories_returnsEmptyArray(): void
    {
        // Arrange
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        // Act
        $result = ($this->categoryTreeBuilder)();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function buildTree_withOnlyRootCategories_returnsRootCategoriesWithoutChildren(): void
    {
        // Arrange
        $category1 = new CategoryReadModel(
            id: new CategoryId('cat-1'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Tech articles'),
            parentId: null,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        $category2 = new CategoryReadModel(
            id: new CategoryId('cat-2'),
            name: new CategoryName('Business'),
            slug: new CategorySlug('business'),
            description: new Description('Business articles'),
            parentId: null,
            order: new Order(2),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 11:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 11:00:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$category1, $category2]);

        // Act
        $result = ($this->categoryTreeBuilder)();

        // Assert
        $this->assertCount(2, $result);
        $this->assertSame('Technology', $result[0]['category']->name->getValue());
        $this->assertEmpty($result[0]['children']);
        $this->assertSame('Business', $result[1]['category']->name->getValue());
        $this->assertEmpty($result[1]['children']);
    }

    #[Test]
    public function buildTree_withHierarchy_returnsNestedStructure(): void
    {
        // Arrange
        $parentId = new CategoryId('parent-1');
        $childId = new CategoryId('child-1');
        $grandChildId = new CategoryId('grandchild-1');

        $parentCategory = new CategoryReadModel(
            id: $parentId,
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Tech articles'),
            parentId: null,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        $childCategory = new CategoryReadModel(
            id: $childId,
            name: new CategoryName('Programming'),
            slug: new CategorySlug('programming'),
            description: new Description('Programming articles'),
            parentId: $parentId,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:30:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:30:00')
            )
        );

        $grandChildCategory = new CategoryReadModel(
            id: $grandChildId,
            name: new CategoryName('PHP'),
            slug: new CategorySlug('php'),
            description: new Description('PHP articles'),
            parentId: $childId,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 11:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 11:00:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$parentCategory, $childCategory, $grandChildCategory]);

        // Act
        $result = ($this->categoryTreeBuilder)();

        // Assert
        $this->assertCount(1, $result); // Only one root category

        // Check parent
        $this->assertSame('Technology', $result[0]['category']->name->getValue());
        $this->assertCount(1, $result[0]['children']); // One child

        // Check child
        $child = $result[0]['children'][0];
        $this->assertEquals('Programming', $child['category']->name->getValue());
        $this->assertCount(1, $child['children']); // One grandchild

        // Check grandchild
        $grandChild = $child['children'][0];
        $this->assertEquals('PHP', $grandChild['category']->name->getValue());
        $this->assertEmpty($grandChild['children']); // No further children
    }

    #[Test]
    public function buildTree_withMultipleRootsAndChildren_returnsSortedHierarchy(): void
    {
        // Arrange
        $root1Id = new CategoryId('root-1');
        $root2Id = new CategoryId('root-2');
        $child1Id = new CategoryId('child-1');
        $child2Id = new CategoryId('child-2');

        // Root categories with different orders
        $root1 = new CategoryReadModel(
            id: $root1Id,
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Tech articles'),
            parentId: null,
            order: new Order(2), // Second in order
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        $root2 = new CategoryReadModel(
            id: $root2Id,
            name: new CategoryName('Business'),
            slug: new CategorySlug('business'),
            description: new Description('Business articles'),
            parentId: null,
            order: new Order(1), // First in order
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        // Children with different orders
        $child1 = new CategoryReadModel(
            id: $child1Id,
            name: new CategoryName('Programming'),
            slug: new CategorySlug('programming'),
            description: new Description('Programming articles'),
            parentId: $root1Id,
            order: new Order(2), // Second in order
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:30:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:30:00')
            )
        );

        $child2 = new CategoryReadModel(
            id: $child2Id,
            name: new CategoryName('Hardware'),
            slug: new CategorySlug('hardware'),
            description: new Description('Hardware articles'),
            parentId: $root1Id,
            order: new Order(1), // First in order
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:30:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:30:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$root1, $root2, $child1, $child2]);

        // Act
        $result = ($this->categoryTreeBuilder)();

        // Assert
        $this->assertCount(2, $result);

        // Check root order: Business (order=1) should come before Technology (order=2)
        $this->assertSame('Business', $result[0]['category']->name->getValue());
        $this->assertSame('Technology', $result[1]['category']->name->getValue());

        // Check children order: Hardware (order=1) should come before Programming (order=2)
        $techChildren = $result[1]['children'];
        $this->assertCount(2, $techChildren);
        $this->assertEquals('Hardware', $techChildren[0]['category']->name->getValue());
        $this->assertEquals('Programming', $techChildren[1]['category']->name->getValue());
    }

    #[Test]
    public function buildTree_withOrphanedCategories_includesOnlyValidHierarchy(): void
    {
        // Arrange - child with non-existent parent should be treated as root
        $parentId = new CategoryId('parent-1');
        $orphanId = new CategoryId('orphan-1');

        $parentCategory = new CategoryReadModel(
            id: $parentId,
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Tech articles'),
            parentId: null,
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:00:00')
            )
        );

        // This category has a parent that doesn't exist in the result set
        $orphanCategory = new CategoryReadModel(
            id: $orphanId,
            name: new CategoryName('Orphaned'),
            slug: new CategorySlug('orphaned'),
            description: new Description('Orphaned category'),
            parentId: new CategoryId('nonexistent-parent'),
            order: new Order(1),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2023-01-01 10:30:00'),
                updatedAt: new \DateTimeImmutable('2023-01-01 10:30:00')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$parentCategory, $orphanCategory]);

        // Act
        $result = ($this->categoryTreeBuilder)();

        // Assert
        // Both categories should be at root level since orphan's parent doesn't exist
        $this->assertCount(2, $result);
        $this->assertSame('Technology', $result[0]['category']->name->getValue());
        $this->assertSame('Orphaned', $result[1]['category']->name->getValue());
    }
}
