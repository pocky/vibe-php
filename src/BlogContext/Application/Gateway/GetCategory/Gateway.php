<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory;

use App\BlogContext\Application\Gateway\GetCategory\Middleware\Processor;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;

#[AsGateway(
    context: 'blog',
    domain: 'category',
    operation: 'get_category',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        DefaultValidation::class,
        Processor::class,
    ],
)]
final class Gateway extends DefaultGateway
{
}
