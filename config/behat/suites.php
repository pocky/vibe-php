<?php

declare(strict_types=1);

use Behat\Config\Config;

return (new Config())
    ->import([
        __DIR__ . '/suites/blog_api.php',
    ]);
