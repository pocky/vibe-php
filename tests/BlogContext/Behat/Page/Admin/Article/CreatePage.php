<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page\Admin\Article;

use App\Tests\Shared\Behat\Context\Page\AbstractCreatePage;

class CreatePage extends AbstractCreatePage
{
    public function getRouteName(): string
    {
        return 'admin_article_create';
    }
}
