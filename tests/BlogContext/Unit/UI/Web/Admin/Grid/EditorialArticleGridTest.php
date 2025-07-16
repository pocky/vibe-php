<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\UI\Web\Admin\Grid;

use App\BlogContext\UI\Web\Admin\Grid\EditorialArticleGrid;
use App\BlogContext\UI\Web\Admin\Resource\EditorialArticleResource;
use PHPUnit\Framework\TestCase;

final class EditorialArticleGridTest extends TestCase
{
    private EditorialArticleGrid $grid;

    protected function setUp(): void
    {
        $this->grid = new EditorialArticleGrid();
    }

    public function testGetName(): void
    {
        $this->assertEquals(EditorialArticleGrid::class, EditorialArticleGrid::getName());
    }

    public function testGetResourceClass(): void
    {
        $this->assertEquals(EditorialArticleResource::class, $this->grid->getResourceClass());
    }
}
