<?php

declare(strict_types=1);

use App\Tests\BlogContext\Behat\Context\Ui\Admin\ManagingArticlesContext;
use App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext;
use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$suite = (new Suite('blog_admin'))
    ->withFilter(new TagFilter('@blog&&@admin'))
    ->withContexts(
        DoctrineORMContext::class,
        ManagingArticlesContext::class,
    )
;

return new Config()->withProfile(
    new Profile('default')->withSuite($suite)
);