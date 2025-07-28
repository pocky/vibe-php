<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\PublishArticle\Middleware;

use App\Blog\Application\Gateway\Article\PublishArticle\Request;
use App\Blog\Application\Gateway\Article\PublishArticle\Response;
use App\Blog\Application\Operation\Command\Article\PublishArticle\Command;
use App\Blog\Application\Operation\Command\Article\PublishArticle\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest): GatewayResponse
    {
        /** @var Request $gatewayRequest */

        // Create command
        $command = new Command(
            articleId: $gatewayRequest->articleId,
            publishAt: $gatewayRequest->publishAt,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            success: true,
            message: 'Article published successfully',
        );
    }
}
