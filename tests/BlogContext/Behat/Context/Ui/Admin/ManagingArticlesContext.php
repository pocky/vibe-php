<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Context\Ui\Admin;

use App\Tests\BlogContext\Behat\Page\Admin\Article\IndexPage;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @final
 */
class ManagingArticlesContext implements Context
{
    public function __construct(
        private readonly IndexPage $indexPage,
    ) {
    }

    #[\Behat\Step\Given('I want to browse articles')]
    public function iWantToBrowseArticles()
    {
        $this->indexPage->open();
    }

    #[\Behat\Step\When('I should see a title')]
    public function iShouldSeeATitle()
    {
        Assert::true($this->indexPage->getTitle());
    }

    #[\Behat\Step\When('I should see a grid')]
    public function iShouldSeeAGrid()
    {
        Assert::true($this->indexPage->getGrid());
    }

    #[\Behat\Step\When('the grid should have columns')]
    public function theGridShouldHaveColumns($columns)
    {
        foreach ($columns as $column) {
            Assert::true($this->indexPage->getColumnFields($column));
        }
    }

    #[\Behat\Step\When('I should see :title in the grid')]
    public function iShouldSeeInTheGrid(string $text)
    {
        Assert::true($this->indexPage->viewArticle($text));
    }
}
