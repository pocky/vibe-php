<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Service;

use App\Tests\BlogContext\Behat\Page\Admin\Article\IndexPage as ArticleIndexPage;
use App\Tests\BlogContext\Behat\Page\Admin\Article\IndexPageInterface as ArticleIndexPageInterface;
use App\Tests\BlogContext\Behat\Page\Admin\Editorial\DashboardPage as EditorialDashboardPage;
use App\Tests\BlogContext\Behat\Page\Admin\Editorial\DashboardPageInterface as EditorialDashboardPageInterface;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

final readonly class PageRegistry
{
    public function __construct(
        private Session $session,
        private RouterInterface $router,
    ) {
    }

    public function getArticleIndexPage(): ArticleIndexPageInterface
    {
        return new ArticleIndexPage($this->session, $this->router);
    }

    public function getEditorialDashboardPage(): EditorialDashboardPageInterface
    {
        return new EditorialDashboardPage($this->session, $this->router);
    }
}
