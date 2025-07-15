<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\SubmitForReview;

use App\BlogContext\Application\Gateway\SubmitForReview\Middleware\Processor;
use App\BlogContext\Application\Gateway\SubmitForReview\Middleware\Validation;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;

#[AsGateway(
    context: 'blog',
    domain: 'article',
    operation: 'submit_for_review',
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
