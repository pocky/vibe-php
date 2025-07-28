<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\DeleteArticle\Middleware;

use App\Blog\Application\Gateway\Article\DeleteArticle\Request;
use App\Blog\Application\Gateway\Article\DeleteArticle\Response;
use App\Blog\Application\Operation\Command\Article\DeleteArticle\Command;
use App\Blog\Application\Operation\Command\Article\DeleteArticle\Handler;
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
        $deleteCommand = new Command(
            articleId: $gatewayRequest->articleId,
            deletedBy: $gatewayRequest->deletedBy,
        );

        // Execute command through handler
        ($this->handler)($deleteCommand);

        // Return success response
        return new Response(
            success: true,
            message: 'Article deleted successfully',
        );
    }
}
