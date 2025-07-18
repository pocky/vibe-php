<?php

declare(strict_types=1);

use Behat\Config\Config;

return (new Config())
    ->import([
        'blog/api.php',      // All API tests for blog context
        'blog/admin.php',    // All admin UI tests for blog context
    ])
;
