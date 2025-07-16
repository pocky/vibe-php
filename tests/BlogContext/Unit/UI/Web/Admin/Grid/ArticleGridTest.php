<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\UI\Web\Admin\Grid;

use App\BlogContext\UI\Web\Admin\Grid\ArticleGrid;
use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use PHPUnit\Framework\TestCase;

final class ArticleGridTest extends TestCase
{
    private ArticleGrid $grid;

    protected function setUp(): void
    {
        $this->grid = new ArticleGrid();
    }

    public function testGetName(): void
    {
        $this->assertEquals(ArticleGrid::class, ArticleGrid::getName());
    }

    public function testGetResourceClass(): void
    {
        $this->assertEquals(ArticleResource::class, $this->grid->getResourceClass());
    }
}
