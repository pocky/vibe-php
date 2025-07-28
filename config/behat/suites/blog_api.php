<?php

declare(strict_types=1);

use Behat\Config\Config;

return new Config([
    'blog_api' => [
        'paths' => ['%paths.base%/features/blog/api'],
        'contexts' => [
            'App\Tests\Blog\Behat\Context\Api\ApiContext',
        ],
    ],
]);