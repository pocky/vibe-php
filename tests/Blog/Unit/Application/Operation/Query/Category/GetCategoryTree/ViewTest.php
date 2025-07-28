<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategoryTree;

use App\Blog\Application\Operation\Query\Category\GetCategoryTree\CategoryNodeView;
use App\Blog\Application\Operation\Query\Category\GetCategoryTree\View;
use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    #[Test]
    public function validView_withEmptyTree_constructsSuccessfully(): void
    {
        $view = new View([]);

        $this->assertEmpty($view->tree);
    }

    #[Test]
    public function validView_withTree_constructsSuccessfully(): void
    {
        $categoryReadModel = new CategoryReadModel(
            id: new CategoryId('category-123'),
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

        $treeNode = [
            'category' => $categoryReadModel,
            'children' => [],
        ];

        $view = new View([$treeNode]);

        $this->assertCount(1, $view->tree);
        $this->assertSame($treeNode, $view->tree[0]);
    }
}

final class CategoryNodeViewTest extends TestCase
{
    #[Test]
    public function validCategoryNodeView_constructsSuccessfully(): void
    {
        $categoryNodeView = new CategoryNodeView(
            id: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology articles',
            parentId: null,
            order: 10,
            createdAt: '2024-01-01T00:00:00+00:00',
            updatedAt: '2024-01-01T00:00:00+00:00',
            children: []
        );

        $this->assertSame('category-123', $categoryNodeView->id);
        $this->assertSame('Technology', $categoryNodeView->name);
        $this->assertSame('technology', $categoryNodeView->slug);
        $this->assertSame('Technology articles', $categoryNodeView->description);
        $this->assertNull($categoryNodeView->parentId);
        $this->assertSame(10, $categoryNodeView->order);
        $this->assertSame('2024-01-01T00:00:00+00:00', $categoryNodeView->createdAt);
        $this->assertSame('2024-01-01T00:00:00+00:00', $categoryNodeView->updatedAt);
        $this->assertEmpty($categoryNodeView->children);
    }
}
