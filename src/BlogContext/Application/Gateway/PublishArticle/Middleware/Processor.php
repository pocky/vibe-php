<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Middleware;

use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\BlogContext\Application\Gateway\PublishArticle\Response;
use App\BlogContext\Application\Operation\Command\PublishArticle\Command;
use App\BlogContext\Application\Operation\Command\PublishArticle\HandlerInterface;
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
            publishAt: $request->publishAt,
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
