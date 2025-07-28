<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\DeleteAuthor\Middleware;

use App\Blog\Application\Gateway\Author\DeleteAuthor\Request;
use App\Blog\Application\Gateway\Author\DeleteAuthor\Response;
use App\Blog\Application\Operation\Command\Author\DeleteAuthor\Command;
use App\Blog\Application\Operation\Command\Author\DeleteAuthor\Handler;
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
            authorId: $gatewayRequest->authorId,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response
        return new Response(
            deleted: true,
        );
    }
}
