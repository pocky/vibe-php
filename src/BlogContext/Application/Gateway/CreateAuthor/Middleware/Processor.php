<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateAuthor\Middleware;

use App\BlogContext\Application\Gateway\CreateAuthor\Request;
use App\BlogContext\Application\Gateway\CreateAuthor\Response;
use App\BlogContext\Application\Operation\Command\CreateAuthor\Command;
use App\BlogContext\Application\Operation\Command\CreateAuthor\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\Generator\UuidGenerator;

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
            name: $request->name,
            email: $request->email,
            bio: $request->bio ?? '',
        );

        // Execute command through handler
        ($this->handler)($command);

        // In a CQRS system with void handlers, the ID is generated inside the domain
        // For simplicity, we generate a placeholder ID here for the response
        // In a real system, you might use event sourcing or return the ID via events
        $authorId = UuidGenerator::generate();

        // Return response with generated data
        return new Response(
            authorId: $authorId,
            success: true,
        );
    }
}
