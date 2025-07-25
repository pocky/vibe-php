<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteAuthor\Middleware;

use App\BlogContext\Application\Gateway\DeleteAuthor\Request;
use App\BlogContext\Application\Gateway\DeleteAuthor\Response;
use App\BlogContext\Application\Operation\Command\DeleteAuthor\Command;
use App\BlogContext\Application\Operation\Command\DeleteAuthor\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // Create command
        $command = new Command(
            authorId: $request->authorId,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response
        return new Response(
            deleted: true,
        );
    }
}
