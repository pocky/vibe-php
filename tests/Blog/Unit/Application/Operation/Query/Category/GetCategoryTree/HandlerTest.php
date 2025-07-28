<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategoryTree;

use App\Blog\Application\Operation\Query\Category\GetCategoryTree\Handler;
use App\Blog\Application\Operation\Query\Category\GetCategoryTree\View;
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

final class HandlerTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);

        // Use real CategoryTreeBuilder with mocked repository
        $categoryTreeBuilder = new CategoryTreeBuilder($this->categoryReadRepository);

        $this->handler = new Handler($categoryTreeBuilder);
    }

    #[Test]
    public function validQuery_withEmptyCategories_returnsEmptyTree(): void
    {
        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = ($this->handler)();

        $this->assertInstanceOf(View::class, $result);
        $this->assertEmpty($result->tree);
    }

    #[Test]
    public function validQuery_withCategories_returnsTreeStructure(): void
    {
        $rootCategory = new CategoryReadModel(
            id: new CategoryId('root-123'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(10),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2024-01-01'),
                updatedAt: new \DateTimeImmutable('2024-01-01')
            )
        );

        $childCategory = new CategoryReadModel(
            id: new CategoryId('child-456'),
            name: new CategoryName('Programming'),
            slug: new CategorySlug('programming'),
            description: new Description('Programming articles'),
            parentId: new CategoryId('root-123'),
            order: new Order(5),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2024-01-01'),
                updatedAt: new \DateTimeImmutable('2024-01-01')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$rootCategory, $childCategory]);

        $result = ($this->handler)();

        $this->assertInstanceOf(View::class, $result);
        $this->assertCount(1, $result->tree); // One root category

        $rootNode = $result->tree[0];
        $this->assertArrayHasKey('category', $rootNode);
        $this->assertArrayHasKey('children', $rootNode);
        $this->assertSame($rootCategory, $rootNode['category']);
        $this->assertCount(1, $rootNode['children']); // One child

        $childNode = $rootNode['children'][0];
        $this->assertArrayHasKey('category', $childNode);
        $this->assertArrayHasKey('children', $childNode);
        $this->assertSame($childCategory, $childNode['category']);
        $this->assertEmpty($childNode['children']); // No grandchildren
    }
}
