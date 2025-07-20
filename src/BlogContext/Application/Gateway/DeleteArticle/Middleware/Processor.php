<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteArticle\Middleware;

use App\BlogContext\Application\Gateway\DeleteArticle\Request;
use App\BlogContext\Application\Gateway\DeleteArticle\Response;
use App\BlogContext\Application\Operation\Command\DeleteArticle\Command;
use App\BlogContext\Application\Operation\Command\DeleteArticle\HandlerInterface;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // Create command
        $command = new Command(
            articleId: $request->articleId,
            deletedBy: $request->deletedBy,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            success: true,
            message: 'Article deleted successfully',
        );
    }
}
