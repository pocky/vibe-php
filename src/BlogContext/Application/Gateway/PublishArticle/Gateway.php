<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle;

use App\BlogContext\Application\Gateway\PublishArticle\Middleware\Processor;
use App\BlogContext\Application\Gateway\PublishArticle\Middleware\SeoValidation;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\Shared\Application\Gateway\Middleware\TranslationErrorHandler;

#[AsGateway(
    context: 'blog',
    domain: 'article',
    operation: 'publish_article',
    middlewares: [
        DefaultLogger::class,
        DefaultErrorHandler::class,
        TranslationErrorHandler::class,
        SeoValidation::class,
        Processor::class,
    ],
)]
final class Gateway extends DefaultGateway
{
}
