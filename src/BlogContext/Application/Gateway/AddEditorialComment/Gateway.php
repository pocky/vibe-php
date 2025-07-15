<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment;

use App\BlogContext\Application\Gateway\AddEditorialComment\Middleware\Processor;
use App\BlogContext\Application\Gateway\AddEditorialComment\Middleware\Validation;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;

#[AsGateway(
    context: 'blog',
    domain: 'editorial',
    operation: 'add_comment',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        Validation::class,
        Processor::class,
    ],
)]
final class Gateway extends DefaultGateway
{
}
