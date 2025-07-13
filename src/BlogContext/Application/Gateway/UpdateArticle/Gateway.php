<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Middleware\Processor;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\{DefaultErrorHandler, DefaultLogger, DefaultValidation};

final class Gateway extends DefaultGateway
{
    public function __construct(
        GatewayInstrumentation $instrumentation,
        DefaultValidation $validation,
        Processor $processor,
    ) {
        $middlewares = [
            new DefaultLogger($instrumentation),
            new DefaultErrorHandler($instrumentation, 'BlogContext', 'Article', 'update'),
            $validation,
            $processor,
        ];

        parent::__construct($middlewares);
    }
}
