<?php

declare(strict_types=1);

use App\Tests\BlogContext\Behat\Context\Api\BlogArticleApiContext;
use App\Tests\Shared\Behat\Context\Hook\DoctrineORMContext;
use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;

$suite = (new Suite('blog_api'))
    ->withFilter(new TagFilter('@blog&&@api'))
    ->withContexts(
        DoctrineORMContext::class,
        MinkContext::class,
        BlogArticleApiContext::class,
    )
;

return new Config()->withProfile(
    new Profile('default')->withSuite($suite)
);