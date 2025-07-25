<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateAuthor\Middleware;

use App\BlogContext\Application\Gateway\UpdateAuthor\Request;
use App\BlogContext\Application\Gateway\UpdateAuthor\Response;
use App\BlogContext\Application\Operation\Command\UpdateAuthor\Command;
use App\BlogContext\Application\Operation\Command\UpdateAuthor\Handler;
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
            name: $request->name,
            email: $request->email,
            bio: $request->bio ?? '',
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response
        return new Response(
            authorId: $request->authorId,
            name: $request->name,
            email: $request->email,
            bio: $request->bio ?? '',
            success: true,
        );
    }
}
