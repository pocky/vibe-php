<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\UpdateAuthor\Middleware;

use App\Blog\Application\Gateway\Author\UpdateAuthor\Request;
use App\Blog\Application\Gateway\Author\UpdateAuthor\Response;
use App\Blog\Application\Operation\Command\Author\UpdateAuthor\Command;
use App\Blog\Application\Operation\Command\Author\UpdateAuthor\Handler;
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
            name: $gatewayRequest->name,
            email: $gatewayRequest->email,
            bio: $gatewayRequest->bio ?? '',
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response
        return new Response(
            authorId: $gatewayRequest->authorId,
            name: $gatewayRequest->name,
            email: $gatewayRequest->email,
            bio: $gatewayRequest->bio ?? '',
            success: true,
        );
    }
}
