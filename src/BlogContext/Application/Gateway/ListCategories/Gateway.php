<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListCategories;

use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;

#[AsGateway(
    context: 'BlogContext',
    domain: 'Category',
    operation: 'List',
    middlewares: [],
)]
final class Gateway extends DefaultGateway
{
    public function __construct(
        Middleware\Processor $processor,
    ) {
        parent::__construct([
            $processor,
        ]);
    }
}
